<?php

namespace App\Tenant;

use App\Tenant\Clients\ClientModel;
use App\Tenant\Clients\ClientRepository;
use PDO;
use Rift\Contracts\Repositories\RepositoriesFactoryInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class RepositoriesFactory implements RepositoriesFactoryInterface {
    public function __construct(
        private PDO $pdo
    ) {}

    public function clients(): OperationOutcome {
        return Operation::success(
            new ClientRepository($this->pdo, new ClientModel)
        );
    }
}