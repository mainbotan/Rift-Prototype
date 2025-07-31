<?php

namespace App\Core\Tenant\UseCases\Account;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class EditPassword implements HandlerInterface {
    public function execute(ServerRequestInterface $request): ResultType 
    {
        $uid = $request->getAttribute('uid');
        return Result::Success($uid);
    }
}