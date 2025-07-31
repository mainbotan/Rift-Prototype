<?php

namespace App\Core\Tenant\UseCases\Deployment;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Database\Configurators\ConfiguratorInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;
use Rift\Crypto\UidManager;
use Rift\Metrics\Stopwatch\StopwatchManager;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Deploy tenant schema
 * 
 * require
 * [uid in request attribute]
 * @version 1.0.0
 */
class DeployTenantSchema implements HandlerInterface {

    const TENANT_SCHEMA_PREFIX = 'tenant_';

    public function __construct(
        private ConfiguratorInterface $configurator,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private UidManager $uidManager
    ) { }
    public function execute(ServerRequestInterface $request): ResultType
    {
        $this->stopwatch->start('deploy_tenant.total');
        
        $uid = $request->getAttribute('uid');
        $uidForSchema = $this->uidManager->toSchemaName($uid);
        
        $this->stopwatch->start('deploy_tenant.reg_schemas');
        $this->configurator->registerTenantModel(\App\Tenant\Clients\ClientModel::class);
        $this->stopwatch->stop('deploy_tenant.reg_schemas');

        $this->stopwatch->start('deploy_tenant.deploy');
        return $this->configurator->forTenant($uidForSchema, self::TENANT_SCHEMA_PREFIX)->configure()
            ->tap(fn() => $this->stopwatch->stop('deploy_tenant.deploy'))

            ->tap(fn() => $this->stopwatch->stop('deploy_tenant.total'))
            ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'deploy_tenant.total'));
    }
}