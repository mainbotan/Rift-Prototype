<?php

namespace App\Examples;

use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class Example01 {
    public function executeOperation(int $id) {
        return $this->getUserById($id)
        ->then(fn($data) => $this->addTimeStamp($data));
    }
    private function addTimeStamp($data): OperationOutcome {
        return Operation::success([
            'user' => $data,
            'timestamp' => time()
        ]);
    }
    private function getUserById(int $id): OperationOutcome 
    {
        // unsuccessful case
        return Operation::error(
            Operation::HTTP_NOT_FOUND, 
            "User {$id} not found"
        );
    }
}