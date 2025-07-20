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
        $box->post('/by-email', App\Core\Tenant\UseCases\Registration\ByEmail\RegistrateByEmail::class)->limit(10);
    });
    $box->group('/auth', function(RoutesBox $box) {
        $box->middleware(App\Core\Middlewares\ParseJsonBody::class);
        $box->post('/by-email', App\Core\Tenant\UseCases\Authorization\ByEmail\AuthByEmail::class)->limit(30);
    });
    $box->group('/account', function(RoutesBox $box) {  
        $box->middleware(App\Core\Tenant\UseCases\Authorization\CheckJwtWithUid::class);    
        $box->post('/verify/by-code', App\Core\Tenant\UseCases\Verification\VerifyByCode::class)->limit(35);
    });
    $box->group('/account', function(RoutesBox $box) {  
        $box->middleware(App\Core\Tenant\UseCases\Authorization\CheckJwtWithUid::class);    
        $box->middleware(App\Core\Tenant\UseCases\Verification\CheckVerified::class);
        $box->post('/deploy', App\Core\Tenant\UseCases\Deployment\DeployTenantSchema::class)->limit(50);
        $box->post('/edit-password', App\Core\Tenant\UseCases\Account\EditPassword::class);
    });


    # Tenant Space
    $box->group('/space', function(RoutesBox $box) {  
        $box->limit(1000);
        $box->middleware(App\Core\Tenant\UseCases\Authorization\CheckJwtWithUid::class);    
        $box->middleware(App\Core\Tenant\UseCases\Verification\CheckVerified::class);

        # Clients CRUD
        $box->post('/clients', App\Tenant\Clients\UseCases\Crud\Create\CreateClient::class)->middleware(App\Core\Middlewares\ParseJsonBody::class);
        $box->get('/clients', App\Tenant\Clients\UseCases\Crud\Get\GetClients::class);
        $box->group('/clients', function(RoutesBox $box) {
            $box->patch('/{uid}', App\Tenant\Clients\UseCases\Crud\Update\UpdateClient::class)->middleware(App\Core\Middlewares\ParseJsonBody::class);
            $box->delete('/{uid}', App\Tenant\Clients\UseCases\Crud\Delete\DeleteClient::class);
            $box->get('/{uid}', App\Tenant\Clients\UseCases\Crud\Get\GetClient::class);
        });

    });
});

$routesBox->group('/testing', function(RoutesBox $box) {
    $box->get('/connector', App\Core\Testing\ConnectorTest::class);
    $box->get('/deploy-schemas', App\Core\Testing\DeployShemas::class);
    $box->get('/orm', App\Core\Testing\ORMTesting::class);
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