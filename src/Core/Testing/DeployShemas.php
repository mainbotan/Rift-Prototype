<?php

namespace App\Core\Testing;

use Rift\Core\Databus\OperationOutcome;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Database\Configurators\ConfiguratorInterface;
use Rift\Contracts\Handlers\HandlerInterface;

class DeployShemas implements HandlerInterface {
    public function __construct(
        private ConfiguratorInterface $configurator
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome 
    {
        $this->configurator::registerSystemModel(\App\Core\Tenant\TenantModel::class);
        return $this->configurator->forSystem()->configure();
    }
}