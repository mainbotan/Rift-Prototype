<?php

namespace App\Addition\Benchmark\Exceptions;

class ExceptionService
{
    public function processSuccess(): string
    {
        return 'OK';
    }
    
    public function processError(): string
    {
        throw new \RuntimeException('Error', 400);
    }
    
    public function processChain(int $depth): int
    {
        return $this->step(0, $depth);
    }
    
    private function step(int $x, int $depth): int
    {
        if ($x >= $depth) {
            throw new \RuntimeException('Max depth reached', 400);
        }
        return $this->step($x + 1, $depth);
    }
}