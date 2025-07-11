<?php

namespace App\Repositories\System;

use PDO;
use Rift\Core\Databus\OperationOutcome;
use Rift\Core\Database\Connect;
use Rift\Core\Repositories\AbstractRouter;

class Router extends AbstractRouter {
    protected string $schema = 'system';

    protected array $repositories = [
        'tenants.repo' => [
            'class' => \App\Repositories\System\TenantsRepository::class,
            'model' => \App\Models\System\Tenants::class
        ] 
    ];
}