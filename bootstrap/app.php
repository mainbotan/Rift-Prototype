<?php
/*
 * |--------------------------------------------------------------------------
 * | Bootstrap App
 * |--------------------------------------------------------------------------
 */
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Rift\Core\Http\Kernel\Kernel;
use Rift\Core\Http\Request\Request;

try {
    require __DIR__ . '/environment.php';

    $container = require __DIR__ . '/dependencies.php';
    
    $kernel = new Kernel($container);
    $request = Request::fromGlobals();

    if (!$request->isSuccess()) {
        throw new RuntimeException('Invalid request: ' . $request->error);
    }

    $response = $kernel->handle($request->result);

} catch (Throwable $e) {
    var_dump($e->getMessage());
}