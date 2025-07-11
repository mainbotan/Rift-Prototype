<?php

namespace App\Addition\Benchmark\OperationOutcome;

use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class OperationService
{
    public function processSuccess(): OperationOutcome
    {
        return Operation::success('OK');
    }
    
    public function processError(): OperationOutcome 
    {
        return Operation::error(400, 'Error');
    }
    
    public function processChain(int $depth): OperationOutcome
    {
        return Operation::success(0)
            ->then(fn($x) => $this->step($x, $depth));
    }
    
    private function step(int $x, int $depth): OperationOutcome
    {
        return $x >= $depth 
            ? Operation::error(400, 'Max depth reached')
            : Operation::success($x + 1)
                ->then(fn($y) => $this->step($y, $depth));
    }
}