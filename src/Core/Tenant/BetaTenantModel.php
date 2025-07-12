<?php

namespace App\Core\Tenant;

use Rift\Core\ORM\Model;

class BetaTenantModel extends Model
{
    public static function configure()
    {
        static::define()
            ->field('id')->type('int')->dbType('INT AUTO_INCREMENT PRIMARY KEY')->apply()
            ->field('name')->type('string')->dbType('VARCHAR(255)')->notNull()->apply()
            ->field('created_at')->type('datetime')->dbType('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->apply();
    }
    
    public static function getTableName(): string
    {
        return 'beta_tenants';
    }
}