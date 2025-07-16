<?php

namespace App\Tenant;

use Rift\Contracts\Database\Bridge\PDO\ConnectorInterface;
use Rift\Contracts\Repositories\RepositoriesRouterInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\UidManager;

class RepositoriesRouter {
    const TENANT_SCHEMA_PREFIX = 'tenant_';

    public function __construct(
        private ConnectorInterface $connector,
        private UidManager $uidManager
    ) { }
    public function factory(string $uid): OperationOutcome {
        return $this->connector->createSchemaConnection(self::TENANT_SCHEMA_PREFIX . $this->uidManager->toSchemaName($uid))
            ->then(function($pdo) {
                return Operation::success(
                    new RepositoriesFactory($pdo)
                );
            });
    }
}