<?php

declare(strict_types=1);

namespace Holerito\integrations\PortalRH;

use Holerito\infraestructure\http\HttpClient;

final class PortalRHClient
{
      private const URL =
      'https://portalrh.rainhadapaz.g12.br/portalrh/Colaboracao/EspelhoDoPonto/EspelhoDoPontoInfo';

      public function __construct(
            private HttpClient $httpClient
      ) {}

      public function getEspelho(
            string $payload,
            string $cookie
      ): string {

            $headers = [
                  'Accept: */*',
                  'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                  'Origin: https://portalrh.rainhadapaz.g12.br',
                  'Referer: https://portalrh.rainhadapaz.g12.br/portalrh/Colaboracao/espelhodoponto',
                  'X-Requested-With: XMLHttpRequest',
                  'Cookie: ' . $cookie,
                  'User-Agent: Mozilla/5.0',
            ];

            return $this->httpClient->post(
                  url: self::URL,
                  headers: $headers,
                  data: [
                        'id' => $payload
                  ]
            );
      }
}
