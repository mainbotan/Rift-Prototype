<?php

namespace App\Core\Tenant;

use Rift\Core\Models\Model;

class TenantModel extends Model {
    
    public static function getTableName(): string { return 'tenants'; }

    public static function getSchema(): array
    {
        return [
            'uid' => [
                'type' => 'int',
                'db_type' => 'SERIAL PRIMARY KEY'
            ],
            'email' => [
                'type' => 'string',
                'min' => 5,
                'max' => 64,
                'required' => true,
                'db_type' => 'VARCHAR(64) NOT NULL UNIQUE',
                'message' => 'Invalid email format',
                'validate' => function($value) {
                    return filter_var($value, FILTER_VALIDATE_EMAIL);
                }
            ],
            'finger' => [
                'type' => 'string',
                'min' => 8,
                'max' => 64,
                'required' => true,
                'db_type' => 'VARCHAR(64) NOT NULL'
            ]
        ];
    }
}