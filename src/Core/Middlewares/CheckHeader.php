<?php

namespace App\Core\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Middlewares\MiddlewareInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Core\Http\Request;

class CheckHeader implements MiddlewareInterface {
    protected $acceptLanguage = 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7';

    public function execute(ServerRequestInterface $request): OperationOutcome
    {
        $header = $request->getHeader('Accept-Language')[0];
        if ($header === $this->acceptLanguage) {
            return Operation::success(null);
        }
        return Operation::error(Operation::HTTP_BAD_REQUEST, 'accept language header not allowed');
    }
}