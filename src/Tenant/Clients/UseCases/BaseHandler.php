<?php

namespace App\Core\Tenant\Clients\Services;

use App\Tenant\RepositoriesFactory;
use App\Core\RepositoriesRouter;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Crypto\UidManager;
use Symfony\Component\Stopwatch\Stopwatch;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

abstract class BaseHandler
{
    protected string $operationKey = 'client';
    protected string $operationAction = 'operation';
    
    public function __construct(
        protected RepositoriesRouter $repositoriesRouter,
        protected Stopwatch $stopwatch,
        protected UidManager $uidManager
    ) {}
    
    protected function initClientRepository(
        ServerRequestInterface $request
    ): ResultType
     {
        $this->startTimer('repo_unit');
        
        return $this->repositoriesRouter
            ->factory($request->getAttribute('uid'))
            ->then(fn(RepositoriesFactory $factory) => $factory->clients())
            ->tap(fn() => $this->stopTimer('repo_unit'))
            ->tap(fn() => $this->startTimer('repo_operation'));
    }
    
    protected function startTimer(string $segment): void
    {
        $this->stopwatch->start("{$this->operationKey}.{$this->operationAction}.{$segment}");
    }
    
    protected function stopTimer(string $segment): void
    {
        $this->stopwatch->stop("{$this->operationKey}.{$this->operationAction}.{$segment}");
    }
}