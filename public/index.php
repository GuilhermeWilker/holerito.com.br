<?php

use Holerito\infraestructure\http\HttpClient;
use Holerito\integrations\PortalRH\parser\Ponto;
use Holerito\integrations\PortalRH\PortalRHClient;
use Holerito\integrations\PortalRH\PortalRHCookies;

session_start();
date_default_timezone_set("America/Sao_Paulo");

require __DIR__ . '/../bootstrap.php';
