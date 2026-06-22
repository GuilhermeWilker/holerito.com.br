<?php

use Holerito\controllers\AuthController;
use Holerito\controllers\HomeController;
use Holerito\controllers\InvoiceController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', HomeController::class . ':index');

// ── Cartão de crédito × salário ──────────────────────────────────────────────
$app->get('/invoice-creditcard',              [InvoiceController::class, 'index']);
$app->post('/invoice-creditcard',             [InvoiceController::class, 'store']);
$app->post('/invoice-creditcard/delete',      [InvoiceController::class, 'destroy']);
$app->post('/invoice-creditcard/settings',    [InvoiceController::class, 'saveSettings']);
$app->get('/api/invoice-stats',               [InvoiceController::class, 'stats']);

// ── Auth ─────────────────────────────────────────────────────────────────────
$app->get('/auth',    [AuthController::class, 'index']);
$app->post('/auth',   [AuthController::class, 'store']);
$app->post('/logout', [AuthController::class, 'destroy']);

$app->post('/add/payload', HomeController::class . ':storePayload');
