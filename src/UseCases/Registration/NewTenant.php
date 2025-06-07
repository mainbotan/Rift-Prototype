<?php

namespace App\UseCases\Registration;

use Rift\Core\Contracts\Operation;
use Rift\Core\Contracts\OperationOutcome;
use Rift\Core\UseCases\UseCaseInterface;

class NewTenant extends Operation implements  UseCaseInterface{
    public function execute(array $data): OperationOutcome {
        return self::success('хуй');
    }
}