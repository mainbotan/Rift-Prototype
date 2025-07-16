<?php

namespace App\Tenant\Clients;

use Rift\Core\Models\Model;

class ClientModel extends Model {
    
    public static function getTableName(): string { return 'clients'; }

    public static function getSchema(): array
    {
        return [
            'uid' => [
                'type' => 'string',
                'min' => 4,
                'max' => 64,
                'db_type' => 'VARCHAR(64) NOT NULL UNIQUE'
            ],
            'email' => [
                'type' => 'string',
                'min' => 5,
                'max' => 64,
                'db_type' => 'VARCHAR(64) NULL UNIQUE',
                'validate' => function($value) {
                    return filter_var($value, FILTER_VALIDATE_EMAIL);
                }
            ],
            'phone' => [
                'type' => 'string',
                'min' => 10,
                'max' => 20,
                'db_type' => 'VARCHAR(20) NULL UNIQUE',
                'validate' => function($value) {
                    return preg_match('/^\+?[\d\s\-\(\)]{10,20}$/', $value);
                }
            ],
            'full_name' => [
                'type' => 'string',
                'min' => 2,
                'max' => 100,
                'db_type' => 'VARCHAR(100) NULL'
            ],
            'created_at' => [
                'type' => 'datetime',
                'db_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'readonly' => true
            ],
            'updated_at' => [
                'type' => 'datetime',
                'db_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'readonly' => true
            ]

        ];
    }
}