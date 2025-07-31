<?php

namespace App\Core\Testing;

use Rift\Core\Databus\ResultType;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Database\Configurators\ConfiguratorInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use App\Core\Tenant\BetaTenantModel;
use App\Core\Testing\Sources\ClientsModel;
use Rift\Core\Databus\Result;
use Rift\Core\ORM\Table;
use Rift\Core\Database\Models\Types;

class ORMTesting implements HandlerInterface {
    public function __construct(
        private ClientsModel $model
    ) { }
    public function execute(ServerRequestInterface $request): ResultType 
    {
        $migration = $this->model->migrate();
        var_dump($migration);
        return Result::Success($migration);
    }
}