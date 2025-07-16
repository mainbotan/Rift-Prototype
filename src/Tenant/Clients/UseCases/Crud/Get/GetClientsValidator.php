<?php

namespace App\Tenant\Clients\UseCases\Crud\Get;

use Rift\Contracts\Validators\ValidatorInterface;
use Rift\Core\Databus\OperationOutcome;
use Rift\Validator\SchemaValidator;

class GetClientsValidator implements ValidatorInterface {
    public function __construct(
        private SchemaValidator $schemaValidator
    ) { }

    private array $queryOptionsSchema = [
        'limit' => [
            'type' => 'int',
            'min' => 10,
            'max' => 10000,
            'default' => 25
        ],
        'offset' => [
            'type' => 'int',
            'min' => 0,
            'max' => 10000,
            'default' => 0
        ]
    ];

    public function validate(array $data): OperationOutcome {
        return $this->schemaValidator->validate($this->queryOptionsSchema, $data);
    }
}