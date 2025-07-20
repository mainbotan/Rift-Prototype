<?php

namespace App\Core\Tenant;

use Rift\Core\ORM\Model;
use Rift\Core\ORM\Types;

class TenantModel extends Model {
    
    const NAME = 'tenants';
    const VERSION = '1.0.9';

    protected function schema(): void {
        $this->table->update('uid')
            ->type(Types::varchar(32))
            ->nullable(false)
            ->affirm();

        $this->table->create('email')
            ->type(Types::varchar(64))
            ->nullable(false)
            ->affirm();

        $this->table->create('hash')
            ->type(Types::varchar(255))
            ->nullable(false)
            ->affirm();
        
        $this->table->create('verify_status')
            ->type(Types::varchar(64))
            ->defaultValue('waiting') 
            ->nullable(false)
            ->affirm();

        $this->table->uniqueIndex(['uid', 'email'])
            ->affirm();
    }
}