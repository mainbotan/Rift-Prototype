<?php

namespace App\Core;

use Rift\Contracts\Database\Bridge\PDO\ConnectorInterface;
use Rift\Contracts\Repositories\RepositoriesRouterInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class RepositoriesRouter implements RepositoriesRouterInterface {
    public function __construct(
        private ConnectorInterface $connector
    ) { }
    public function factory(): OperationOutcome {
        return $this->connector->createSchemaConnection('system')
            ->then(function($pdo) {
                return Operation::success(
                    new RepositoriesFactory($pdo)
                );
            });
    }
}