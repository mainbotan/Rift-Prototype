<?php

/*
 * |--------------------------------------------------------------------------
 * |
 * Routes Box
 * |
 * |--------------------------------------------------------------------------
 */

use Rift\Core\Http\RoutesBox;

$routesBox = new RoutesBox();

$routesBox->post("/manage/reg", App\UseCases\Registration\NewTenant::class, []);
$routesBox->post("/manage/resetPassword", App\UseCases\Registration\NewTenant::class, []);
$routesBox->post("/manage/changePlan", App\UseCases\Registration\NewTenant::class, []);

$routesBox->get("/tenant/services/service/{uid}", App\UseCases\Registration\NewTenant::class, []);

return $routesBox;