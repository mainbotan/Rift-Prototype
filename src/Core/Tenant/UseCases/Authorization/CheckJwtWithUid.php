<?php

namespace App\Core\Tenant\UseCases\Authorization;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Middlewares\MiddlewareInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\JwtManager;

class CheckJwtWithUid implements MiddlewareInterface {
    public function __construct(
        private JwtManager $jwtManager
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+([a-zA-Z0-9\-_]+?\.[a-zA-Z0-9\-_]+?\.[a-zA-Z0-9\-_]+?)$/i', $authHeader, $matches)) {
            return Operation::error(Operation::HTTP_UNAUTHORIZED, 'A valid authorization token was not found.');
        }

        $token = $matches[1];
        
        return $this->jwtManager->decode($token)
            ->then(function ($jwtData) use ($request) {

                if (empty($jwtData['uid'])) {
                    return Operation::error(Operation::HTTP_UNAUTHORIZED, 'Invalid token: missing UID');
                }
                
                $currentTime = time();
                if (!isset($jwtData['exp']) || $jwtData['exp'] < $currentTime) {
                    return Operation::error(Operation::HTTP_UNAUTHORIZED, 'Token expired');
                }

                if (isset($jwtData['iat']) && $jwtData['iat'] > $currentTime) {
                    return Operation::error(Operation::HTTP_UNAUTHORIZED, 'Invalid token issuance time');
                }
                
                return Operation::success(
                    $request->withAttribute('uid', $jwtData['uid'])
                );
            })
            ->catch(function ($error) {
                return Operation::error(
                    Operation::HTTP_UNAUTHORIZED,
                    'Token verification failed: ' . $error
                );
            });
    }
}