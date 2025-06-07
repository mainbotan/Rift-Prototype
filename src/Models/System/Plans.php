<?php

namespace App\Models\System;

use Rift\Core\Models\AbstractModel;

class Plans extends AbstractModel
{
    public static function getSchema(): array
    {
        return [
            'id' => [
                'type' => 'int',
                'db_type' => 'SERIAL PRIMARY KEY'
            ],
            'name' => [
                'type' => 'string',
                'min' => 2,
                'max' => 32,
                'required' => true,
                'db_type' => 'VARCHAR(32) NOT NULL'
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
}