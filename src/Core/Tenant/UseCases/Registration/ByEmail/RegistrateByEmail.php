<?php

namespace App\Core\Tenant\UseCases\Registration\ByEmail;

use App\Core\RepositoriesFactory;
use App\Core\RepositoriesRouter;
use App\Core\Tenant\Services\MailerService;
use App\Core\Tenant\TenantRepository;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Crypto\JwtManager;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\EncryptionManager;
use Rift\Crypto\HashManager;
use Rift\Crypto\UidManager;
use Symfony\Component\Stopwatch\Stopwatch;
use Rift\Metrics\Stopwatch\StopwatchManager;

/**
 * POST email 
 * POST password
 * @version 1.0.0
 */
class RegistrateByEmail implements HandlerInterface {
    public function __construct(
        private RegistrateByEmailValidator $validator,
        private RepositoriesRouter $repositoriesRouter,
        private UidManager $uidManager,
        private JwtManager $jwtManager,
        private HashManager $hashManager,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private MailerService $mailer,
        private EncryptionManager $encryptionManager
    ) { }

    public function execute(ServerRequestInterface $request): OperationOutcome {

        // stopwatch
        $this->stopwatch->start('reg.total');

        $requestBody = $request->getParsedBody();

        $this->stopwatch->start('reg.validation');
        return $this->validator->validate($requestBody)
            ->tap(fn() => $this->stopwatch->stop('reg.validation'))                                                  
                                 
            ->tap(fn() => $this->stopwatch->start('reg.repo_unit')) 
            ->then(fn() => $this->repositoriesRouter->factory())
            ->then(fn(RepositoriesFactory $factory) => $factory->tenants())
            ->tap(fn() => $this->stopwatch->stop('reg.repo_unit'))                         
            
            ->ensure(
                function(TenantRepository $repository) use ($requestBody) {
                    $this->stopwatch->start('reg.email_check');
                    $existing = $repository->getTenantUidByEmail($requestBody['email'])->result;
                    $this->stopwatch->stop('reg.email_check');

                    return !isset($existing[0]['uid']);
                },
                'A client with the same email already exists. If it was you, log in to access your account.',
                Operation::HTTP_CONFLICT
            )
            ->then(function(TenantRepository $repository) use ($requestBody) {

                $this->stopwatch->start('reg.uid_gen');
                $uid = $this->uidManager->generate();
                $this->stopwatch->stop('reg.uid_gen');

                $this->stopwatch->start('reg.hash');
                $hash = $this->hashManager->passwordHash($requestBody['password']);
                $this->stopwatch->stop('reg.hash');

                return $repository->createTenant([
                    'uid' => $uid,
                    'email' => $requestBody['email'],
                    'hash' => $hash
                ])
                ->map(fn() => ['uid' => $uid]);
            })

            ->tap(fn() => $this->stopwatch->start('reg.jwt_gen'))
            ->then(function(array $jwtData) {
                return $this->jwtManager->encode($jwtData)
                    ->map(fn($token) => [
                        'auth' => [
                            'token' => $token
                        ],
                        'uid' => $jwtData['uid']
                    ]);
            })
            ->tap(fn() => $this->stopwatch->stop('reg.jwt_gen'))

            ->then(function($result) use ($requestBody) {

                $this->stopwatch->start('reg.verify_code_gen');
                $verifyCode = random_int(100000, 999999);
                $this->stopwatch->stop('reg.verify_code_gen');

                $this->stopwatch->start('reg.verify_code_encrypt');
                return $this->encryptionManager->encrypt($verifyCode)
                    ->tap(fn() => $this->stopwatch->stop('reg.verify_code_encrypt'))

                    ->tap(fn() => $this->stopwatch->start('reg.verify_code_send'))
                    ->then(function($encryptedVerifyCode) use ($requestBody, $verifyCode) {
                        $this->mailer->sendConfirmationEmail($requestBody['email'], $verifyCode);
                        return Operation::success($encryptedVerifyCode);
                    })
                    ->tap(fn() => $this->stopwatch->stop('reg.verify_code_send'))

                    ->then(function ($encryptedVerifyCode) use ($result) {
                        return $this->jwtManager->encode([
                            'uid' => $result['uid'],
                            'code' => $encryptedVerifyCode
                        ]);
                    })
                    ->map(fn($verifyToken) => [
                        'auth' => $result['auth'],
                        'verify' => [
                            'token' => $verifyToken
                        ]
                    ])
                    ->tap(fn() => $this->stopwatch->stop('reg.total'))
                    ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'reg.total'));
            })
            ->catch(function($error, $code) {
                return Operation::error($code, "Registration failed: $error")
                    ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'reg.total'));
            });
    }
}