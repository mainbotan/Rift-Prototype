<?php

namespace App\Core\Tenant\UseCases\Verification;

use Rift\Contracts\Validators\ValidatorInterface;
use Rift\Core\Databus\ResultType;
use Rift\Validator\SchemaValidator;

class VerifyByCodeValidator implements ValidatorInterface {
    public function __construct(
        private SchemaValidator $schemaValidator
    ) { }

    private array $requestBodySchema = [
        'code' => [
            'type' => 'string',
            'min' => 6,
            'max' => 6
        ]
    ];

    public function validate(array $data): ResultType {
        return $this->schemaValidator->validate($this->requestBodySchema, $data);
    }
}