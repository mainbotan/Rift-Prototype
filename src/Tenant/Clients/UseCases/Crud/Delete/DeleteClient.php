<?php

namespace App\Tenant\Clients\UseCases\Crud\Delete;

use App\Tenant\Clients\ClientModel;
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

class DeleteClient implements HandlerInterface {
    public function __construct(
        private ClientModel $model,
        private RepositoriesRouter $repositoriesRouter,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private UidManager $uidManager
    ) { }

    public function execute(ServerRequestInterface $request): ResultType {
        $this->stopwatch->start('client.delete.total');

        $paramsFromRoute = $request->getAttribute('params');
        if (!isset($paramsFromRoute['uid'])) {
            return Result::Failure(Result::HTTP_BAD_REQUEST, 'The required uid route parameter is lost.');
        }
        $uid = $paramsFromRoute['uid'];

        $this->stopwatch->start('client.delete.repo_unit');
        return $this->repositoriesRouter->factory($request->getAttribute('uid'))
                ->then(fn(RepositoriesFactory $factory) => $factory->clients())
                ->tap(fn() => $this->stopwatch->stop('client.delete.repo_unit'))
                    
                ->tap(fn() => $this->stopwatch->start('client.delete.repo_request'))
                ->then(function (ClientRepository $repository) use ($uid) {

                    $this->stopwatch->start('client.delete.repo_check_uid');
                    return $repository->checkClientByUid($uid)
                        ->tap(fn() => $this->stopwatch->stop('client.delete.repo_check_uid'))

                        ->then(function ($result) use ($repository, $uid) {
                            if ($result !== true) {
                                return Result::Failure(Result::HTTP_NOT_FOUND, "Client not found.");
                            }
                            $this->stopwatch->start('client.delete.repo_delete_client');
                            return $repository->deleteClientByUid($uid)
                                ->tap(fn() => $this->stopwatch->stop('client.delete.repo_delete_client'));
                        });
                })
                ->tap(fn() => $this->stopwatch->stop('client.delete.repo_request'))
                ->catch(function($error, $code) {
                    return Result::Failure($code, "Delete operation failed: $error");
                })
                ->tap(fn() => $this->stopwatch->stop('client.delete.total'))
                ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, 'client.delete.total'));
    }
}