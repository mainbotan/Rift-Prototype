<?php

namespace App\Core\Tenant\UseCases\Registration;

use Rift\Contracts\Validators\ValidatorInterface;
use Rift\Core\Databus\OperationOutcome;
use Rift\Validator\SchemaValidator;

class RegistrateByEmailValidator implements ValidatorInterface {
    public function __construct(
        private SchemaValidator $schemaValidator
    ) { }

    private array $requestBodySchema = [
        'email' => [
            'type' => 'string',
            'min' => 4,
            'max' => 64
        ],
        'password' => [
            'type' => 'string',
            'min' => 4,
            'max' => 64
        ]
    ];

    public function validate(array $data): OperationOutcome {
        return $this->schemaValidator->validate($this->requestBodySchema, $data);
    }
}