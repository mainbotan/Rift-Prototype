<?php

namespace App\Tenant\Clients\UseCases\Crud\Get;

use App\Tenant\Clients\ClientRepository;
use App\Tenant\RepositoriesFactory;
use App\Tenant\RepositoriesRouter;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;
use Symfony\Component\Stopwatch\Stopwatch;
use Rift\Metrics\Stopwatch\StopwatchManager;
use Rift\Crypto\UidManager;

class GetClients implements HandlerInterface {
    public function __construct(
        private GetClientsValidator $validator,
        private RepositoriesRouter $repositoriesRouter,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private UidManager $uidManager
    ) { }

    public function execute(ServerRequestInterface $request): ResultType {
        $this->stopwatch->start('clients.get.total');

        $queryParams = $request->getQueryParams();

        $this->stopwatch->start('clients.get.validation');
        return $this->validator->validate($queryParams)
            ->then(function($validatedData) use ($request) { 
                $this->stopwatch->stop('clients.get.validation');
                
                $this->stopwatch->start('clients.get.repo_unit');
                return $this->repositoriesRouter->factory($request->getAttribute('uid'))
                    ->then(fn(RepositoriesFactory $factory) => $factory->clients())
                    ->tap(fn() => $this->stopwatch->stop('clients.get.repo_unit'))
                    
                    ->tap(fn() => $this->stopwatch->start('clients.get.repo_request'))
                    ->then(function (ClientRepository $repository) use ($validatedData) {
                        return $repository->getClients($validatedData);
                    })
                    ->tap(fn() => $this->stopwatch->stop('clients.get.repo_request'));
            })
            ->tap(fn() => $this->stopwatch->stop('clients.get.total'))
            ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'clients.get.total'))
            ->catch(function($error, $code) {
                return Result::Failure($code, "Get operation failed: $error");
            });
    }
}