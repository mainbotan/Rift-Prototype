<?php

namespace App\Core\Tenant\UseCases\Verification;

use App\Core\RepositoriesFactory;
use App\Core\RepositoriesRouter;
use App\Core\Tenant\TenantRepository;
use App\Core\Tenant\UseCases\Deployment\DeployTenantSchema;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\EncryptionManager;
use Rift\Crypto\JwtManager;
use Rift\Metrics\Stopwatch\StopwatchManager;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * GET token [verify token with encrypted code + uid]
 * X-Verification-Code [verification code]
 * @version 1.0.0
 */
class VerifyByCode implements HandlerInterface {

    const VERIFIED_STATUS = 'verified';
    
    const VERIFY_HEADER = 'X-Verification-Code';

    public function __construct(
        private VerifyByCodeValidator $validator,
        private RepositoriesRouter $repositoriesRouter,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private JwtManager $jwtManager,
        private EncryptionManager $encryptionManager,
        private DeployTenantSchema $deployTenant
    )
    { }
    public function execute(ServerRequestInterface $request): OperationOutcome
    {
        // stopwatch
        $this->stopwatch->start('verify.total');

        // checking GET token
        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['token'])) {
            return Operation::error(Operation::HTTP_BAD_REQUEST, 'Verify token lost.');
        }
        $verifyToken = $queryParams['token'];
        
        $clientCode = $request->getHeader(self::VERIFY_HEADER)[0];
        
        $this->stopwatch->start('verify.validation');
        return $this->validator->validate(['code' => $clientCode])
            ->tap(fn() => $this->stopwatch->stop('verify.validation'))                                                  

            ->tap(fn() => $this->stopwatch->start('verify.token_decode'))
            ->then(function() use ($verifyToken) {
                return $this->jwtManager->decode($verifyToken);
            })
            ->tap(fn() => $this->stopwatch->stop('verify.token_decode'))    

            ->tap(fn() => $this->stopwatch->start('verify.token_check_exp'))
            ->ensure(function($jwtData) {   
                return $this->jwtManager->checkExpiration($jwtData);
            }, 'Verification token expired.', Operation::HTTP_UNAUTHORIZED)
            ->tap(fn() => $this->stopwatch->stop('verify.token_check_exp'))
            
            ->then(function ($jwtData) use ($clientCode) {

                $uid = $jwtData['uid'];

                $this->stopwatch->start('verify.code_decode');
                return $this->encryptionManager->decrypt($jwtData['code'])
                    ->tap(fn() => $this->stopwatch->stop('verify.code_decode'))

                    ->tap(fn() => $this->stopwatch->start('verify.match_codes'))
                    ->then(function($decodedCode) use ($clientCode, $jwtData) {
                        if ((string) $decodedCode !== (string) $clientCode) {
                            return Operation::error(Operation::HTTP_UNAUTHORIZED, 'Mismatch of verification codes.');
                        }
                        return Operation::success($jwtData['uid']);
                    })
                    ->tap(fn() => $this->stopwatch->stop('verify.match_codes'))
                    
                    ->tap(fn() => $this->stopwatch->start('verify.repo_unit')) 
                    ->then(fn() => $this->repositoriesRouter->factory())
                    ->then(fn(RepositoriesFactory $factory) => $factory->tenants())
                    ->tap(fn() => $this->stopwatch->stop('verify.repo_unit'))

                    ->tap(fn() => $this->stopwatch->start('verify.switch_verify_status'))
                    ->then(function(TenantRepository $repository) use ($uid) {
                        return $repository->updateVerifyStatus($uid, self::VERIFIED_STATUS);
                    })
                    ->tap(fn() => $this->stopwatch->stop('verify.switch_verify_status'))

                    ->tap(fn() => $this->stopwatch->stop('verify.total'))
                    ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'verify.total'));
            })

            ->catch(function($error, $code) {
                return Operation::error($code, "Verify failed: $error")
                    ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'verify.total'));
            });                         
    }
}