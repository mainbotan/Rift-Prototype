<?php

namespace App\Examples;

use Rift\Core\Databus\Operation;
use Rift\Core\Databus\OperationOutcome;

class Example02 {
    public static function getObject(): OperationOutcome {
        return Operation::success(
            result: [
                'id' => 10,
                'name' => 'Alice'
            ],
            debug: ['init_source' => 'user_service']
        )
        ->withMetric('start_time', microtime(true));
    }
}