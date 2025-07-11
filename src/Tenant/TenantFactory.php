<?php

namespace App\Tenant;

use PDO;
use Rift\Contracts\Models\ModelInterface;
use Rift\Contracts\Repositories\FactoryInterface;

/**
 * Tenant repository factory. 
 * Methods for configuring specific repositories.
 * 
 * @version 1.0.0
 */
class TenantFactory implements FactoryInterface {
    public function __construct(
        private PDO $pdo
    ) { }

    // methods collection

    public function users() {}
}