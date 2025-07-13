<?php

namespace App\Core\Tenant\UseCases\Account;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class EditPassword implements HandlerInterface {
    public function execute(ServerRequestInterface $request): OperationOutcome 
    {
        $uid = $request->getAttribute('uid');
        return Operation::success($uid);
    }
}