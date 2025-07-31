<?php

namespace App\Core\Tenant\UseCases\Authorization;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Middlewares\MiddlewareInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;
use Rift\Crypto\JwtManager;

class CheckJwtWithUid implements MiddlewareInterface {
    public function __construct(
        private JwtManager $jwtManager
    ) { }
    public function execute(ServerRequestInterface $request): ResultType
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+([a-zA-Z0-9\-_]+?\.[a-zA-Z0-9\-_]+?\.[a-zA-Z0-9\-_]+?)$/i', $authHeader, $matches)) {
            return Result::Failure(Result::HTTP_UNAUTHORIZED, 'A valid authorization token was not found.');
        }

        $token = $matches[1];
        
        return $this->jwtManager->decode($token)
            ->then(function ($jwtData) use ($request) {

                if (empty($jwtData['uid'])) {
                    return Result::Failure(Result::HTTP_UNAUTHORIZED, 'Invalid token: missing UID');
                }
                
                $currentTime = time();
                if (!isset($jwtData['exp']) || $jwtData['exp'] < $currentTime) {
                    return Result::Failure(Result::HTTP_UNAUTHORIZED, 'Token expired');
                }

                if (isset($jwtData['iat']) && $jwtData['iat'] > $currentTime) {
                    return Result::Failure(Result::HTTP_UNAUTHORIZED, 'Invalid token issuance time');
                }
                
                return Result::Success(
                    $request->withAttribute('uid', $jwtData['uid'])
                );
            })
            ->catch(function ($error) {
                return Result::Failure(
                    Result::HTTP_UNAUTHORIZED,
                    'Token verification failed: ' . $error
                );
            });
    }
}