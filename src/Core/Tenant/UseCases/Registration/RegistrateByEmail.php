<?php

namespace App\Core\Tenant\UseCases\Registration;

use App\Core\RepositoriesFactory;
use App\Core\RepositoriesRouter;
use App\Core\Tenant\TenantRepository;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Crypto\JwtManager;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\UID;

class RegistrateByEmail implements HandlerInterface {
    public function __construct(
        private RegistrateByEmailValidator $validator,
        private RepositoriesRouter $repositoriesRouter,
        private UID $UID,
        private JwtManager $jwtManager
    ) { }

    public function execute(ServerRequestInterface $request): OperationOutcome {
        $requestBody = $request->getParsedBody();
        
        return $this->validator->validate($requestBody)
            
            ->then(fn() => $this->repositoriesRouter->factory())
            ->then(fn(RepositoriesFactory $factory) => $factory->tenants())
            ->ensure(
                function(TenantRepository $repository) use ($requestBody) {
                    $existing = $repository->getTenantUidByEmail($requestBody['email'])->result;
                    return !isset($existing[0]['uid']);
                },
                'A client with the same email already exists. If it was you, log in to access your account.',
                Operation::HTTP_CONFLICT
            )
            ->then(function(TenantRepository $repository) use ($requestBody) {
                $uid = $this->UID->generate();
                return $repository->createTenant([
                    'uid' => $uid,
                    'email' => $requestBody['email'],
                    'finger' => $requestBody['finger']
                ])
                ->map(fn() => ['uid' => $uid]); // Преобразуем успешный результат
            })
            ->then(function(array $jwtData) {
                return $this->jwtManager->encode($jwtData)
                    ->map(fn($token) => [
                        'token' => $token,
                        'uid' => $jwtData['uid']
                    ]);
            })
            ->catch(function($error, $code) {
                return Operation::error($code, "Registration failed: $error");
            });
    }
}