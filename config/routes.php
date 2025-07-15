<?php

/*
 * |--------------------------------------------------------------------------
 * |
 * configs/routes.php
 * Routes Box
 * |
 * |--------------------------------------------------------------------------
 */

use Rift\Core\Http\RoutesBox\RoutesBox;

$routesBox = new RoutesBox();

# Tenant
$routesBox->group('/v1', function(RoutesBox $box) {
    $box->limit(100);
    $box->middleware(Rift\Core\Http\RateLimiter\RateLimitMiddleware::class);

    $box->group('/reg', function(RoutesBox $box) {
        $box->middleware(App\Core\Middlewares\ParseJsonBody::class);
        $box->post('/byEmail', App\Core\Tenant\UseCases\Registration\ByEmail\RegistrateByEmail::class)->limit(10);
    });
    $box->group('/auth', function(RoutesBox $box) {
        $box->middleware(App\Core\Middlewares\ParseJsonBody::class);
        $box->post('/byEmail', App\Core\Tenant\UseCases\Authorization\ByEmail\AuthByEmail::class)->limit(30);
    });
    $box->group('/verify', function(RoutesBox $box) {
        $box->post('/email', App\Core\Tenant\UseCases\Verification\VerifyByCode::class)->limit(5);
    });
    $box->group('/account', function(RoutesBox $box) {  
        $box->middleware(App\Core\Tenant\UseCases\Authorization\CheckJwtWithUid::class);
        $box->post('/editPassword', App\Core\Tenant\UseCases\Account\EditPassword::class);
    });
});

$routesBox->group('/testing', function(RoutesBox $box) {
    $box->get('/connector', App\Core\Testing\ConnectorTest::class);
    $box->get('/deploy-schemas', App\Core\Testing\DeployShemas::class);
});

// $routesBox->get('/tokenInfo', App\UseCases\TokenInfo::class)
//     ->middleware(App\Middlewares\CheckHeader::class)
//     ->middleware(App\Middlewares\AuthClient::class);

// $routesBox->group('/account', function (RoutesBox $box) {
//     $box->middleware(App\Middlewares\AuthClient::class);
//     $box->group('/users', function (RoutesBox $box) {
//         $box->get('/all', 'handler');
//         $box->get('/{id}/update', 'handler');
//     });
// });

return $routesBox;