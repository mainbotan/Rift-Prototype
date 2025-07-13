<?php

namespace App\Core;

use App\Core\Tenant\TenantModel;
use App\Core\Tenant\TenantRepository;
use PDO;
use Rift\Contracts\Repositories\RepositoriesFactoryInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class RepositoriesFactory implements RepositoriesFactoryInterface {
    public function __construct(
        private PDO $pdo
    ) {}

    // Просто создаёт репозиторий, без OperationOutcome
    public function tenants(): OperationOutcome {
        return Operation::success(
            new TenantRepository($this->pdo, new TenantModel)
        );
    }
}