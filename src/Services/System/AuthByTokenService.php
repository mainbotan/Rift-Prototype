<?php

namespace App\Services\System;

use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class AuthByTokenService {
    public function checkToken(string $token): OperationOutcome {
        return Operation::success(null);
    }
}