<?php

namespace App\Models\System;

use Rift\Core\Models\AbstractModel;

class Tenants extends AbstractModel
{
    public static function getSchema(): array
    {
        return [
            'id' => [
                'type' => 'string',
                'min' => 14,
                'max' => 14,
                'db_type' => 'VARCHAR(14) PRIMARY KEY'
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
            'password' => [
                'type' => 'string',
                'min' => 8,
                'max' => 256,
                'required' => true,
                'db_type' => 'VARCHAR(256) NOT NULL'
            ],
            'status' => [
                'type' => 'string',
                'enum' => ['active', 'banned'],
                'default' => 'active',
                'db_type' => 'VARCHAR(20) DEFAULT \'active\''
            ],
            'plan_id' => [
                'type' => 'int',
                'db_type' => 'INTEGER'
            ],
            'created_at' => [
                'type' => 'string',
                'db_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
            ],
            'updated_at' => [
                'type' => 'string',
                'db_type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
            ]
        ];
    }
    
    public static function getIndexes(): array
    {
        return [
            [
                'columns' => ['status'],
                'name' => 'idx_tenant_status'
            ],
            [
                'columns' => ['created_at'],
                'unique' => false
            ]
        ];
    }
    
    public static function getForeignKeys(): array
    {
        return [
            [
                'column' => 'plan_id',
                'reference_table' => 'plans',
                'reference_column' => 'id',
                'on_delete' => 'SET NULL',
                'name' => 'fk_tenant_plan'
            ]
        ];
    }
}