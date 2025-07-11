<?php

namespace App\Core\Testing;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Database\Bridge\PDO\ConnectorInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\OperationOutcome;

class ConnectorTest implements HandlerInterface {
    public function __construct(
        private ConnectorInterface $connector
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome 
    {
        $connectResult = $this->connector->createAdminConnection();
        return $connectResult;
    }
}