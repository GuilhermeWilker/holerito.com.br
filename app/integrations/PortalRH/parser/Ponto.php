<?php

declare(strict_types=1);

namespace Holerito\integrations\PortalRH\parser;

final class Ponto
{
      public function parser(string $data): array
      {
            preg_match(
                  '/var oModeloEspelhoDoPonto = (.*?);/s',
                  $data,
                  $matches
            );

            if (!isset($matches[1])) {
                  throw new \Exception('JSON do espelho de ponto não encontrado.');
            }

            $data = json_decode($matches[1], true);

            if (!$data) {
                  throw new \Exception("Erro ao converter JSON.");
            }

            return $data;
      }
}
