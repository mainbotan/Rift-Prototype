<?php

namespace App\Addition\Examples;

use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class Example01 {
    public function executeOperation(int $id) {
        return $this->getUserById($id)
        ->then(fn($data) => $this->addTimeStamp($data));
    }
    private function addTimeStamp($data): ResultType {
        return Result::Success([
            'user' => $data,
            'timestamp' => time()
        ]);
    }
    private function getUserById(int $id): ResultType 
    {
        // unsuccessful case
        return Result::Failure(
            Result::HTTP_NOT_FOUND, 
            "User {$id} not found"
        );
    }
}