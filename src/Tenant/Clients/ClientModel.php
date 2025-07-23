<?php

namespace App\Tenant\Clients;

use Rift\Core\Database\Models\Model;
use Rift\Core\Database\Models\Types;

class ClientModel extends Model {

    const NAME = 'clients';
    const VERSION = '1.0.8';

    public function schema(): void
    {
        $this->table->create('uid')
            ->type(Types::varchar(64))
            ->nullable(false)
            ->validation([
                'type' => 'string',
                'min' => 4,
                'max' => 64
            ])
            ->affirm();
        
        $this->table->update('email')
            ->type(Types::varchar(64))
            ->nullable(true)
            ->validation([
                'type' => 'string',
                'min' => 4,
                'max' => 64,
                'validate' => function($value) {
                    return filter_var($value, FILTER_VALIDATE_EMAIL);
                },
                'default' => null,
                'optional' => null
            ])
            ->affirm();

        $this->table->create('phone')
            ->type(Types::varchar(20))
            ->nullable(false)
            ->validation([
                'type' => 'string',
                'min' => 10,
                'max' => 20,
                'validate' => function($value) {
                    return preg_match('/^\+?[\d\s\-\(\)]{10,20}$/', $value);
                },
                'default' => null,
                'optional' => true
            ])
            ->nullable(true)
            ->affirm();

        $this->table->create('full_name')
            ->type(Types::varchar(100))
            ->validation([
                'type' => 'string',
                'min' => 2,
                'max' => 100
            ])
            ->nullable(true)
            ->affirm();

        $this->table->create('created_at')
            ->type(Types::CREATED_AT)
            ->affirm();

        $this->table->create('updated_at')
            ->type(Types::CREATED_AT)
            ->affirm();
        
        $this->table->uniqueIndex(['uid', 'phone', 'email'])
            ->affirm();
    }
}