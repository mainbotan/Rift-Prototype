<?php

namespace App\Tenant\Clients\UseCases\Crud\Create;

use App\Tenant\Clients\ClientModel;
use App\Tenant\Clients\ClientRepository;
use App\Tenant\RepositoriesFactory;
use App\Tenant\RepositoriesRouter;
use Psr\Http\Message\ServerRequestInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Symfony\Component\Stopwatch\Stopwatch;
use Rift\Metrics\Stopwatch\StopwatchManager;
use Rift\Crypto\UidManager;

class CreateClient implements HandlerInterface {
    public function __construct(
        private ClientModel $model,
        private RepositoriesRouter $repositoriesRouter,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private UidManager $uidManager
    ) { }

    public function execute(ServerRequestInterface $request): OperationOutcome {
        $this->stopwatch->start('client.create.total');

        $requestBody = $request->getParsedBody();

        $this->stopwatch->start('client.create.validation');
        return $this->model->validate($requestBody)
            ->then(function($validatedData) use ($request) { 
                $this->stopwatch->stop('client.create.validation');
                
                $this->stopwatch->start('client.create.repo_unit');
                return $this->repositoriesRouter->factory($request->getAttribute('uid'))
                    ->then(fn(RepositoriesFactory $factory) => $factory->clients())
                    ->tap(fn() => $this->stopwatch->stop('client.create.repo_unit'))
                    
                    ->tap(fn() => $this->stopwatch->start('client.create.repo_request'))
                    ->then(function (ClientRepository $repository) use ($validatedData) {
                        $validatedData['uid'] = $this->uidManager->generate();
                        return $repository->createClient($validatedData);
                    })
                    ->tap(fn() => $this->stopwatch->stop('client.create.repo_request'));
            })
            ->catch(function($error, $code) {
                return Operation::error($code, "Create operation failed: $error");
            })
            ->tap(fn() => $this->stopwatch->stop('client.create.total'))
            ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'client.create.total'));
    }
}