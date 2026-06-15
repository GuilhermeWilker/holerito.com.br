<?php

use Holerito\controllers\AuthController;
use Holerito\controllers\HomeController;
use Holerito\controllers\WalletController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', HomeController::class . ':index');

$app->get('/auth',    [AuthController::class, 'index']);
$app->post('/auth',   [AuthController::class, 'store']);
$app->post('/logout', [AuthController::class, 'destroy']);

$app->post("/add/payload", HomeController::class . ':storePayload');
