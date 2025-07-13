<?php

namespace App\Core\Tenant\UseCases\Registration;

use App\Core\RepositoriesFactory;
use App\Core\RepositoriesRouter;
use App\Core\Tenant\TenantRepository;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\UID;

class RegistrateByEmail implements HandlerInterface {
    public function __construct(
        private RegistrateByEmailValidator $validator,
        private RepositoriesRouter $repositoriesRouter,
        private UID $UID
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome {
        $requestBody = $request->getParsedBody();
        
        $result = $this->validator->validate($requestBody)

            ->then(fn() => $this->repositoriesRouter->factory())

            ->then(fn(RepositoriesFactory $factory) => $factory->tenants())

            ->then(function(TenantRepository $repository) use ($requestBody) {
                $uid = $this->UID->generate();
                return $repository->createTenant([
                    'uid' => $uid,
                    'email' => $requestBody['email'],
                    'finger' => $requestBody['finger']
                ]);
            });
        
        return $result;
    }
}