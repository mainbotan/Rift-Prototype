<?php

namespace App\Core\Tenant\UseCases\Registration\ByEmail;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

/**
 * GET token [verify token with encrypted code + uid]
 * @version 1.0.0
 */
class VerifyEmail implements HandlerInterface {
    public function execute(ServerRequestInterface $request): OperationOutcome
    {
        $verifyToken = $request->getQueryParams();
        return Operation::success($verifyToken);
    }
}