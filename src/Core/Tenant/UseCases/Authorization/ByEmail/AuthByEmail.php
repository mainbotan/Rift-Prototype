<?php

namespace App\Core\Tenant\UseCases\Authorization\ByEmail;

use App\Core\RepositoriesRouter;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\ResultType;
use Rift\Crypto\JwtManager;
use Rift\Metrics\Stopwatch\StopwatchManager;
use Symfony\Component\Stopwatch\Stopwatch;
use App\Core\RepositoriesFactory;
use App\Core\Tenant\TenantRepository;
use App\Core\Tenant\UseCases\Registration\ByEmail\GenerateVerifyJwtTokenValidator;
use App\Core\Tenant\UseCases\Registration\ByEmail\RegistrateByEmailValidator;
use Rift\Core\Databus\Result;

class AuthByEmail implements HandlerInterface {
    const AUTH_TOKEN_TTL = 3600 * 24;

    public function __construct(
        private RegistrateByEmailValidator $validator,
        private RepositoriesRouter $repositoriesRouter,
        private JwtManager $jwtManager,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager
    ) { }
    public function execute(ServerRequestInterface $request): ResultType
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
                        return Result::Failure(Result::HTTP_NOT_FOUND, 'Account not found.');
                    } 
                    $hash = $tenantData[0]['hash'];

                    $this->stopwatch->start('auth.hash_verify');
                    if (!password_verify($requestBody['password'], $hash)) {
                        return Result::Failure(Result::HTTP_FORBIDDEN, 'Invalid password.');
                    }
                    $this->stopwatch->stop('auth.hash_verify');

                    return Result::Success([
                        'uid' => $tenantData[0]['uid']
                    ]);
                }
            )
            
            ->tap(fn() => $this->stopwatch->start('auth.jwt_gen'))
            ->then(function(array $jwtData) {
                return $this->jwtManager->encode($jwtData, self::AUTH_TOKEN_TTL)
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
                return Result::Failure($code, "Authorization failed: $error")
                    ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'auth.total'));
            });
    }
}