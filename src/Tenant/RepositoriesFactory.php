<?php

namespace App\Tenant;

use App\Tenant\Clients\ClientModel;
use App\Tenant\Clients\ClientRepository;
use PDO;
use Rift\Contracts\Repositories\RepositoriesFactoryInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class RepositoriesFactory implements RepositoriesFactoryInterface {
    public function __construct(
        private PDO $pdo
    ) {}

    public function clients(): ResultType {
        return Result::Success(
            new ClientRepository($this->pdo, new ClientModel)
        );
    }
}