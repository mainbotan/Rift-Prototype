<?php

namespace App\Core\Tenant\UseCases\Verification;

use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Middlewares\MiddlewareInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Rift\Crypto\JwtManager;
use App\Core\RepositoriesFactory;
use App\Core\RepositoriesRouter;
use App\Core\Tenant\TenantRepository;
use PHPUnit\Framework\Constraint\Operator;

/**
 * Deploy tenant schema
 * 
 * require
 * [uid in request attribute]
 * @version 1.0.0
 */
class CheckVerified implements MiddlewareInterface {
    
    const VERIFIED_STATUS = 'verified';

    public function __construct(
        private JwtManager $jwtManager,
        private RepositoriesRouter $repositoriesRouter,
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome
    {   
        return Operation::success($request->getAttribute('uid'))
            ->then(function ($uid) {
                if ($uid === null) {
                    return Operation::error(Operation::HTTP_UNAUTHORIZED, 'The authorization token could not be recognized.');
                }
                return $this->repositoriesRouter->factory()
                    ->then(fn(RepositoriesFactory $factory) => $factory->tenants())
                    ->then(function (TenantRepository $repository) use ($uid) {
                        return $repository->getTenantVerifyStatusByUid($uid)
                            ->then(function ($result) {
                                if ($result[0]['verify_status'] !== self::VERIFIED_STATUS) {
                                    return Operation::error(Operation::HTTP_UNAUTHORIZED, 'The account has not been verified.');
                                }
                                return Operation::success(null);
                            });
                    });
            })
            ->catch(function ($error) {
                return Operation::error(
                    Operation::HTTP_UNAUTHORIZED,
                    'Account verification error: ' . $error
                );
            });
    }
}