<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();

require __DIR__ . '/routes/web.php';
require __DIR__ . '/config/config.php';

function hoursToDecimal(string $time): float
{
      [$hours, $minutes] = explode(',', $time);

      return (int)$hours + ((int)$minutes / 60);
}
function __moneyFormatter($amount, string $currency = 'pt_BR', string $isoCode = 'BRL')
{
      $formatter = new NumberFormatter($currency, NumberFormatter::CURRENCY);
      return $formatter->formatCurrency($amount, $isoCode);
}

$app->run();
