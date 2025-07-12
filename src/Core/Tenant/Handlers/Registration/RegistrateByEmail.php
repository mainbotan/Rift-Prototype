<?php

namespace App\Core\Tenant\Handlers\Registration;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class RegistrateByEmail implements HandlerInterface {
    public function execute(ServerRequestInterface $request): OperationOutcome {
        return Operation::success(
            $request->getBody()
        );
    }
}