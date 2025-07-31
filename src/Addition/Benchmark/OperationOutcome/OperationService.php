<?php

namespace App\Addition\Benchmark\ResultType;

use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class OperationService
{
    public function processSuccess(): ResultType
    {
        return Result::Success('OK');
    }
    
    public function processError(): ResultType 
    {
        return Result::Failure(400, 'Error');
    }
    
    public function processChain(int $depth): ResultType
    {
        return Result::Success(0)
            ->then(fn($x) => $this->step($x, $depth));
    }
    
    private function step(int $x, int $depth): ResultType
    {
        return $x >= $depth 
            ? Result::Failure(400, 'Max depth reached')
            : Result::Success($x + 1)
                ->then(fn($y) => $this->step($y, $depth));
    }
}