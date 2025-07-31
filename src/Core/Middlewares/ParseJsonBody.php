<?php

namespace App\Core\Middlewares;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Middlewares\MiddlewareInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class ParseJsonBody implements MiddlewareInterface 
{
    public function execute(ServerRequestInterface $request): ResultType
    {
        $jsonBody = $request->getBody()->getContents();

        try {
            $parsedBody = json_decode($jsonBody, true, 512, JSON_UNESCAPED_UNICODE);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }

            // Создаем новый запрос с распарсенным телом
            $newRequest = $request->withParsedBody($parsedBody);
            
            return Result::Success($newRequest);
            
        } catch (Exception $e) {
            return Result::Failure(Result::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}