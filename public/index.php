<?php

/*
 * |--------------------------------------------------------------------------
 * |
 * This file is generated automatically by Rift Miniframework
 * |
 * The entry point of your application.
 * |
 * |--------------------------------------------------------------------------
 */

declare(strict_types=1);
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// autoload
require_once __DIR__ . '/../vendor/autoload.php';

// imports
use Dotenv\Dotenv;
use Rift\Core\Contracts\Operation;
use Rift\Core\Contracts\OperationOutcome;
use Rift\Core\Navigation\QueryExecute;
use Rift\Core\Http\Request;
use Rift\Core\Http\Router;


// .env preparing
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->safeLoad();

// creating request object
$requestObjectRequest = Request::fromGlobals();

if (!$requestObjectRequest->isSuccess()) {
    http_response_code(400);
    exit();
}

$requestObject = $requestObjectRequest->result;

// routes box
$routesBox = require_once 'routes/box.php';

// router request
$router = Router::fromRoutesBox($routesBox);
$resultQuery = $router->execute($requestObject);

$result = $resultQuery->toJson(fn($result) => [
    'ok' => $result->isSuccess(),
    'code' => $result->code,
    'payload' => $result->error,
    '_meta' => $result->meta
]);
var_dump($result);

var_dump('Application initialized successful');