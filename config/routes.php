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

# single

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