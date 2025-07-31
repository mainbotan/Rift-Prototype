<?php

namespace App\Core;

use Rift\Contracts\Database\Bridge\PDO\ConnectorInterface;
use Rift\Contracts\Repositories\RepositoriesRouterInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class RepositoriesRouter implements RepositoriesRouterInterface {
    public function __construct(
        private ConnectorInterface $connector
    ) { }
    public function factory(): ResultType {
        return $this->connector->createSchemaConnection('system')
            ->then(function($pdo) {
                return Result::Success(
                    new RepositoriesFactory($pdo)
                );
            });
    }
}