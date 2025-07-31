<?php

namespace App\Core\Tenant\UseCases\Registration\ByEmail;

use Rift\Contracts\Validators\ValidatorInterface;
use Rift\Core\Databus\ResultType;
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

    public function validate(array $data): ResultType {
        return $this->schemaValidator->validate($this->requestBodySchema, $data);
    }
}