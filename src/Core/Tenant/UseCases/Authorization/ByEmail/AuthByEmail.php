<?php

namespace App\Core\Tenant\UseCases\Authorization\ByEmail;

use App\Core\RepositoriesRouter;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\JwtManager;
use Rift\Metrics\Stopwatch\StopwatchManager;
use Symfony\Component\Stopwatch\Stopwatch;
use App\Core\RepositoriesFactory;
use App\Core\Tenant\TenantRepository;
use App\Core\Tenant\UseCases\Registration\ByEmail\GenerateVerifyJwtTokenValidator;
use App\Core\Tenant\UseCases\Registration\ByEmail\RegistrateByEmailValidator;
use Rift\Core\Databus\Operation;

class AuthByEmail implements HandlerInterface {
    public function __construct(
        private RegistrateByEmailValidator $validator,
        private RepositoriesRouter $repositoriesRouter,
        private JwtManager $jwtManager,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome
    {
        // stopwatch
        $this->stopwatch->start('auth.total');

        $requestBody = $request->getParsedBody();

        $this->stopwatch->start('auth.validation');
        return $this->validator->validate($requestBody)
            ->tap(fn() => $this->stopwatch->stop('auth.validation'))   

            ->tap(fn() => $this->stopwatch->start('auth.repo_unit')) 
            ->then(fn() => $this->repositoriesRouter->factory())
            ->then(fn(RepositoriesFactory $factory) => $factory->tenants())
            ->tap(fn() => $this->stopwatch->stop('auth.repo_unit'))                         
            
            ->then(
                function(TenantRepository $repository) use ($requestBody) {
                    $this->stopwatch->start('auth.hash_request');
                    $tenantData = $repository->getTenantUidAndHashByEmail($requestBody['email'])->result;
                    $this->stopwatch->stop('auth.hash_request');

                    if (!isset($tenantData[0]['uid'])) {
                        return Operation::error(Operation::HTTP_NOT_FOUND, 'Account not found.');
                    } 
                    $hash = $tenantData[0]['hash'];

                    $this->stopwatch->start('auth.hash_verify');
                    if (!password_verify($requestBody['password'], $hash)) {
                        return Operation::error(Operation::HTTP_FORBIDDEN, 'Invalid password.');
                    }
                    $this->stopwatch->stop('auth.hash_verify');

                    return Operation::success([
                        'uid' => $tenantData[0]['uid']
                    ]);
                }
            )
            
            ->tap(fn() => $this->stopwatch->start('auth.jwt_gen'))
            ->then(function(array $jwtData) {
                return $this->jwtManager->encode($jwtData)
                    ->map(fn($token) => [
                        'auth' => [
                            'token' => $token   
                        ]
                    ])
                    ->tap(fn() => $this->stopwatch->stop('auth.jwt_gen'))
                    ->tap(fn() => $this->stopwatch->stop('auth.total'))
                    ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'auth.total'));
            })
            ->catch(function($error, $code) {
                return Operation::error($code, "Authorization failed: $error")
                    ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'auth.total'));
            });
    }
}