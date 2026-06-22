<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Inicia sessão antes de qualquer coisa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$app = AppFactory::create();

// Middleware para parsing de body (forms POST)
$app->addBodyParsingMiddleware();

require __DIR__ . '/routes/web.php';
require __DIR__ . '/config/config.php';

function hoursToDecimal(string $time): float
{
    [$hours, $minutes] = explode(',', $time);
    return (int) $hours + ((int) $minutes / 60);
}

function __moneyFormatter($amount, string $currency = 'pt_BR', string $isoCode = 'BRL')
{
    $formatter = new NumberFormatter($currency, NumberFormatter::CURRENCY);
    return $formatter->formatCurrency((float) $amount, $isoCode);
}

$app->run();
