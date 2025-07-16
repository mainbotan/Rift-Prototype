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

class GetClient implements HandlerInterface {

    private static string $operationKey = 'client';
    private static string $operationAction = 'get';

    public function __construct(
        private ClientModel $model,
        private RepositoriesRouter $repositoriesRouter,
        private Stopwatch $stopwatch,
        private StopwatchManager $stopwatchManager,
        private UidManager $uidManager
    ) { }

    public function execute(ServerRequestInterface $request): OperationOutcome {
        $this->stopwatch->start("{$this::$operationKey}.{$this::$operationAction}.total");

        $paramsFromRoute = $request->getAttribute('params');
        if (!isset($paramsFromRoute['uid'])) {
            return Operation::error(Operation::HTTP_BAD_REQUEST, 'The required uid route parameter is lost.');
        }
        $uid = $paramsFromRoute['uid'];

        $this->stopwatch->start("{$this::$operationKey}.{$this::$operationAction}.repo_unit");
        return $this->repositoriesRouter->factory($request->getAttribute('uid'))
                ->then(fn(RepositoriesFactory $factory) => $factory->clients())
                ->tap(fn() => $this->stopwatch->stop("{$this::$operationKey}.{$this::$operationAction}.repo_unit"))
                    
                ->tap(fn() => $this->stopwatch->start("{$this::$operationKey}.{$this::$operationAction}.repo_requests"))
                ->then(function (ClientRepository $repository) use ($uid) {

                    $this->stopwatch->start("{$this::$operationKey}.{$this::$operationAction}.repo_check_uid");
                    return $repository->checkClientByUid($uid)
                        ->tap(fn() => $this->stopwatch->stop("{$this::$operationKey}.{$this::$operationAction}.repo_check_uid"))

                        ->then(function ($result) use ($repository, $uid) {
                            if ($result !== true) {
                                return Operation::error(Operation::HTTP_NOT_FOUND, "Client not found.");
                            }
                            $this->stopwatch->start("{$this::$operationKey}.{$this::$operationAction}.repo_get_client");
                            return $repository->getClientByUid($uid)
                                ->tap(fn() => $this->stopwatch->stop("{$this::$operationKey}.{$this::$operationAction}.repo_get_client"));
                        });
                })
                ->tap(fn() => $this->stopwatch->stop("{$this::$operationKey}.{$this::$operationAction}.repo_requests"))
                ->catch(function($error, $code) {
                    return Operation::error($code, "{$this::$operationAction} operation failed: $error");
                })
                ->tap(fn() => $this->stopwatch->stop("{$this::$operationKey}.{$this::$operationAction}.total"))
                ->withMetric('stopwatch', $this->stopwatchManager->collectMetrics($this->stopwatch, "{$this::$operationKey}.{$this::$operationAction}.total"));
    }
}