<?php

/*
 * |--------------------------------------------------------------------------
 * | Dependency Container
 * |--------------------------------------------------------------------------
 */

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

$container = (function(): ContainerInterface {
    try {
        $builder = new ContainerBuilder();
        
        if ($_ENV['APP_ENV'] === 'prod') {
            $builder->enableCompilation(__DIR__ . '/../var/cache');
            $builder->writeProxiesToFile(true, __DIR__ . '/../var/proxies');
        }
        
        $builder->addDefinitions(require __DIR__ . '/../config/di/app.php');
        return $builder->build();
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/../var/logs/bootstrap_error.log', $e->getMessage());
        throw $e;
    }
})();

return $container;