<?php

namespace App\Middlewares;

use Rift\Core\Databus\OperationOutcome;
use Rift\Core\Http\Request;
use App\Services\System\AuthByTokenService;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Middlewares\MiddlewareInterface;
use Rift\Core\Databus\Operation;

class AuthClient implements MiddlewareInterface {
    public function __construct(
        private AuthByTokenService $AuthByTokenService    # auxiliary service
    ) {}        

    public function execute(ServerRequestInterface $request): OperationOutcome 
    {   
        # Logic chain
        $result = $this->getToken($request)
            ->then(fn($token) => $this->AuthByTokenService->checkToken($token));   
        return $result;
    }

    /**
     * getToken from requestObject
     */
    protected function getToken(ServerRequestInterface $request): OperationOutcome {
        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['token'])) {
            return Operation::error(Operation::HTTP_UNAUTHORIZED, 'api token lost');   
        }
        return Operation::success($queryParams['token']);
    }
}