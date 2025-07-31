<?php

namespace App\Addition\Examples;

use Rift\Core\Databus\Result;
use Rift\Core\Databus\ResultType;

class Example02 {
    public static function getObject(): ResultType {
        return Result::Success(
            result: [
                'id' => 10,
                'name' => 'Alice'
            ],
            debug: ['init_source' => 'user_service']
        )
        ->withMetric('start_time', microtime(true));
    }
}