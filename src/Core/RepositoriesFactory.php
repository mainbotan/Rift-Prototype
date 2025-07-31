<?php

namespace App\Core;

use App\Core\Tenant\TenantModel;
use App\Core\Tenant\TenantRepository;
use PDO;
use Rift\Contracts\Repositories\RepositoriesFactoryInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class RepositoriesFactory implements RepositoriesFactoryInterface {
    public function __construct(
        private PDO $pdo
    ) {}
    public function tenants(): ResultType {
        return Result::Success(
            new TenantRepository($this->pdo, new TenantModel)
        );
    }
}