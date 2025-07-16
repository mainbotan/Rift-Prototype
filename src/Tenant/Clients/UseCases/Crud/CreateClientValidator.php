<?php

namespace App\Tenant\Clients\UseCases\Crud;

use App\Tenant\Clients\ClientModel;
use Rift\Contracts\Validators\ValidatorInterface;
use Rift\Core\Databus\OperationOutcome;
use Rift\Validator\SchemaValidator;

class CreateClientValidator implements ValidatorInterface {
    public function __construct(
        private ClientModel $model
    ) { }

    public function validate(array $data): OperationOutcome {
        return $this->model->validateField('email', $data['email'] ?? null)
            ->then(fn() => $this->model->validateField('phone', $data['phone'] ?? null))
            ->then(fn() => $this->model->validateField('full_name', $data['full_name'] ?? null));
    }
}