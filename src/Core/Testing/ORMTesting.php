<?php

namespace App\Core\Testing;

use Rift\Core\Databus\OperationOutcome;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Database\Configurators\ConfiguratorInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use App\Core\Tenant\BetaTenantModel;
use App\Core\Testing\Sources\ClientsModel;
use Rift\Core\Databus\Operation;
use Rift\Core\ORM\Table;
use Rift\Core\Database\Models\Types;

class ORMTesting implements HandlerInterface {
    public function __construct(
        private ClientsModel $model
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome 
    {
        $migration = $this->model->migrate();
        var_dump($migration);
        return Operation::success($migration);
    }
}