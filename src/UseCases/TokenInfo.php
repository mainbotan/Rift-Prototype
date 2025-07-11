<?php

namespace App\UseCases;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rift\Contracts\Handlers\HandlerInterface;
use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;
use Symfony\Component\Stopwatch\Stopwatch;

class TokenInfo implements HandlerInterface {
    public function __construct(
        private Stopwatch $stopwatch
    ) { }
    public function execute(ServerRequestInterface $request): OperationOutcome {
        $this->stopwatch->start('token-info-operation');

        $token = $request->getQueryParams()['token'];
        $x = 0;

        while ($x < 1000000) {
            $x++;
        }
        
        $event = $this->stopwatch->stop('token-info-operation');
        $eventData = [
            'duration.ms' => $event->getDuration(),
            'memory.bytes'=> $event->getMemory(),
            'periods' => array_map(
                fn($period) => ['start' => $period->getStartTime(), 'end' => $period->getEndTime()],
                $event->getPeriods()
            )
        ];

        return Operation::success($token, $eventData);
    }
}