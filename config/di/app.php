<?php

use Rift\Contracts\Cache\CacheInterface;
use Rift\Contracts\Http\ResponseEmitter\EmitterInterface;
use Rift\Contracts\Http\Router\RouterInterface;
use Rift\Contracts\Http\RoutesBox\RoutesBoxInterface;
use Rift\Core\Cache\Redis\RedisCacheService;
use Rift\Core\Http\ResponseEmitters\CompositeEmitter;
use Rift\Core\Http\Router\Router;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

use function DI\autowire;
use function DI\get;

$bonding = [
    // Интерфейсы → реализации
    EmitterInterface::class => get(CompositeEmitter::class),
    RouterInterface::class => get(Router::class),
    RoutesBoxInterface::class => require __DIR__ . '/../routes.php',
    MailerInterface::class => get(Mailer::class),
    CacheInterface::class => get(RedisCacheService::class),

    // Symfony Mailer
    TransportInterface::class => function () {
        return Transport::fromDsn($_ENV['MAILER_DSN']);
    },
    Mailer::class => autowire()
        ->constructorParameter('transport', get(TransportInterface::class)),
];

$defaultRealisation = require __DIR__ . '/../../vendor/rift/core/di/rift.php';
$diConfiguration = array_merge($bonding, $defaultRealisation);

return $diConfiguration;