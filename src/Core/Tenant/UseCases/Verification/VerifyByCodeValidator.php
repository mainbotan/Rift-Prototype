<?php

namespace App\Core\Tenant\UseCases\Verification;

use Rift\Contracts\Validators\ValidatorInterface;
use Rift\Core\Databus\OperationOutcome;
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

    public function validate(array $data): OperationOutcome {
        return $this->schemaValidator->validate($this->requestBodySchema, $data);
    }
}