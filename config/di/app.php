<?php

use Rift\Contracts\Http\ResponseEmitter\EmitterInterface;
use Rift\Contracts\Http\Router\RouterInterface;
use Rift\Contracts\Http\RoutesBox\RoutesBoxInterface;
use Rift\Core\Http\ResponseEmitters\CompositeEmitter;
use Rift\Core\Http\Router\Router;

use function DI\autowire;
use function DI\get;

$bonding = [
    // Интерфейсы → реализации
    EmitterInterface::class => get(CompositeEmitter::class),
    RouterInterface::class => get(Router::class),
    RoutesBoxInterface::class => require __DIR__ . '/../routes.php',
];

$defaultRealisation = require __DIR__ . '/../../vendor/rift/core/di/rift.php';
$diConfiguration = array_merge($bonding, $defaultRealisation);

return $diConfiguration;