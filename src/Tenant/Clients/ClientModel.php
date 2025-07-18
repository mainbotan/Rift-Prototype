<?php

namespace App\Tenant\Clients;

use Rift\Core\Models\Model;

class ClientModel extends Model {
    
    public static function getTableName(): string { return 'clients'; }

    public static function getVersion(): string { return '1.0.2'; }

    public static function getSchema(): array
    {
        return [
            'uid' => [
                'type' => 'string',
                'min' => 4,
                'max' => 64,
                'db_type' => 'VARCHAR(64) NOT NULL UNIQUE',
                'default' => null
            ],
            'email' => [
                'type' => 'string',
                'min' => 5,
                'max' => 64,
                'db_type' => 'VARCHAR(64) NULL UNIQUE',
                'validate' => function($value) {
                    return filter_var($value, FILTER_VALIDATE_EMAIL);
                },
                'default' => null,
                'optional' => true
            ],
            'phone' => [
                'type' => 'string',
                'min' => 10,
                'max' => 20,
                'db_type' => 'VARCHAR(20) NULL UNIQUE',
                'validate' => function($value) {
                    return preg_match('/^\+?[\d\s\-\(\)]{10,20}$/', $value);
                },
                'default' => null,
                'optional' => true
            ],
            'full_name' => [
                'type' => 'string',
                'min' => 2,
                'max' => 100,
                'db_type' => 'VARCHAR(100) NULL'
            ],
            'notes' => [
                'type' => 'string',
                'min' => 2,
                'max' => 1024,
                'db_type' => 'VARCHAR(1024) NULL',
                'default' => null,
                'optional' => true
            ],
            'source' => [
                'type' => 'string',
                'min' => 2,
                'max' => 32,
                'db_type' => 'VARCHAR(32) NULL',
                'default' => null,
                'optional' => true
            ],
            'profit' => [
                'type' => 'float',
                'db_type' => 'DECIMAL(15,2) NULL',
                'default' => null,
                'optional' => true
            ],
            'created_at' => [
                'type' => 'datetime',
                'db_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'readonly' => true,
                'optional' => true
            ],            
            'updated_at' => [
                'type' => 'datetime',
                'db_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'readonly' => true,
                'optional' => true
            ]
        ];
    }
}