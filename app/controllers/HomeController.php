<?php

declare(strict_types=1);

namespace Holerito\controllers;

use Holerito\infraestructure\http\HttpClient;
use Holerito\integrations\PortalRH\parser\Ponto;
use Holerito\integrations\PortalRH\PortalRHClient;
use Holerito\integrations\PortalRH\view\EspelhoDePonto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController extends Controller
{
      private const DEFAULT_PAYLOAD = '0001;1331;20-01-2026;19-02-2026;S';
      private const SESSION_KEY     = 'selected_payload';

      private function currentPayload(): string
      {
            return $_SESSION[self::SESSION_KEY] ?? self::DEFAULT_PAYLOAD;
      }

      public function index(Request $request, Response $response, array $args): Response
      {
            // Redireciona para login se os cookies não estiverem na sessão
            $cookies = AuthController::cookiesFromSession();

            if ($cookies === null) {
                  return $response
                        ->withHeader('Location', '/auth')
                        ->withStatus(302);
            }

            $httpClient     = new HttpClient();
            $portalRHClient = new PortalRHClient($httpClient);

            $resPonto = $portalRHClient->getEspelho(
                  $this->currentPayload(),
                  $cookies->get()
            );

            $data = new Ponto();
            $data = $data->parser($resPonto);

            $espelhoPonto = new EspelhoDePonto();

            $html = $this->render('index', [
                  'data'           => $espelhoPonto->make($data),
                  'payloads'       => $this->availablePayloads(),
                  'currentPayload' => $this->currentPayload(),
            ]);

            $response->getBody()->write($html);

            return $response;
      }

      public function storePayload(Request $request, Response $response, array $args): Response
      {
            $payload = trim($request->getParsedBody()['payload'] ?? '');

            if ($payload !== '') {
                  $_SESSION[self::SESSION_KEY] = $payload;
            }

            return $response
                  ->withHeader('Location', '/')
                  ->withStatus(302);
      }

      private function availablePayloads(): array
      {
            $payloads = [];
            $now      = new \DateTimeImmutable();

            $months = [
                  'Janeiro'   => ['20-12-2025', '19-01-2026'],
                  'Fevereiro' => ['20-01-2026', '19-02-2026'],
                  'Março'     => ['20-02-2026', '19-03-2026'],
                  'Abril'     => ['20-03-2026', '19-04-2026'],
                  'Maio'      => ['20-04-2026', '19-05-2026'],
                  'Junho'     => ['20-05-2026', '19-06-2026'],
                  'Julho'     => ['20-06-2026', '19-07-2026'],
                  'Agosto'    => ['20-07-2026', '19-08-2026'],
                  'Setembro'  => ['20-08-2026', '19-09-2026'],
                  'Outubro'   => ['20-09-2026', '19-10-2026'],
                  'Novembro'  => ['20-10-2026', '19-11-2026'],
                  'Dezembro'  => ['20-11-2026', '19-12-2026'],
            ];

            foreach ($months as $label => [$start, $end]) {
                  $endDate     = \DateTimeImmutable::createFromFormat('d-m-Y', $end);
                  $availableAt = $endDate->setDate(
                        (int) $endDate->format('Y'),
                        (int) $endDate->format('m'),
                        14
                  );

                  if ($now >= $availableAt) {
                        $payloads[$label] = "0001;1331;{$start};{$end};S";
                  }
            }

            return $payloads;
      }
}
