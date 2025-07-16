<?php

namespace App\Tenant\Clients\UseCases\Crud;

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

class UpdateClient implements HandlerInterface {
    public function __construct(
        private ClientModel $model,
        private RepositoriesRouter $repositoriesRouter,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private UidManager $uidManager
    ) { }

    public function execute(ServerRequestInterface $request): OperationOutcome {
        $this->stopwatch->start('client.update.total');

        $requestBody = $request->getParsedBody();
        $paramsFromRoute = $request->getAttribute('params');
        if (!isset($paramsFromRoute['uid'])) {
            return Operation::error(Operation::HTTP_BAD_REQUEST, 'The required uid route parameter is lost.');
        }
        $uid = $paramsFromRoute['uid'];
        $requestBody['uid'] = $uid;

        $this->stopwatch->start('client.update.validation');
        return $this->model->validate($requestBody)
            ->then(function($validatedData) use ($request) { 
                $this->stopwatch->stop('client.update.validation');
                
                $this->stopwatch->start('client.update.repo_unit');
                return $this->repositoriesRouter->factory($request->getAttribute('uid'))
                    ->then(fn(RepositoriesFactory $factory) => $factory->clients())
                    ->tap(fn() => $this->stopwatch->stop('client.update.repo_unit'))
                    
                    ->tap(fn() => $this->stopwatch->start('client.update.repo_request'))
                    ->then(function (ClientRepository $repository) use ($validatedData) {
                        
                        $this->stopwatch->start('client.update.repo_check_uid');
                        return $repository->checkClientByUid($validatedData['uid'])
                            ->tap(fn() => $this->stopwatch->stop('client.update.repo_check_uid'))

                            ->then(function ($result) use ($repository, $validatedData) {
                                if ($result !== true) {
                                    return Operation::error(Operation::HTTP_NOT_FOUND, "Client not found.");
                                }
                                $this->stopwatch->start('client.update.repo_update_client');
                                return $repository->dynamicUpdateClient($validatedData)
                                    ->tap(fn() => $this->stopwatch->stop('client.update.repo_update_client'))

                                    ->tap(fn() => $this->stopwatch->start('client.update.repo_get_updated_client'))
                                    ->then(function () use ($validatedData, $repository) {
                                        return $repository->getClientByUid($validatedData['uid'])
                                            ->tap(fn() => $this->stopwatch->stop('client.update.repo_get_updated_client'));
                                    });
                            });
                    })
                    ->tap(fn() => $this->stopwatch->stop('client.update.repo_request'));
            })
            ->catch(function($error, $code) {
                return Operation::error($code, "Update operation failed: $error");
            })
            ->tap(fn() => $this->stopwatch->stop('client.update.total'))
            ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'client.update.total'));
    }
}