<?php

namespace App\Core\Testing;

use App\Core\Tenant\TenantModel;
use Rift\Core\Databus\OperationOutcome;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Database\Migrations\DispatcherInterface;
use Rift\Contracts\Handlers\HandlerInterface;

class DeployShemas implements HandlerInterface {
    public function __construct(
        private DispatcherInterface $dispatcher
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome 
    {
        $result = $this->dispatcher
            ->model(TenantModel::class)
            ->forSystem()
            ->configure();

        var_dump($this->dispatcher->logs);
        return $result;
        
    }
}