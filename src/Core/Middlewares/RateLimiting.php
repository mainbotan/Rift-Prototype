<?php

namespace App\Core\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Middlewares\MiddlewareInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Core\Http\Request;

class RateLimiting implements MiddlewareInterface {

    public function execute(ServerRequestInterface $request): OperationOutcome
    {
        $route = $request->getAttribute('route');
        var_dump($route);
        return Operation::success(null);
    }
}