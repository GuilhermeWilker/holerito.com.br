<?php

declare(strict_types=1);

namespace Holerito\infraestructure\http;

use RuntimeException;

final class HttpClient
{
      public function post(
            string $url,
            array $headers = [],
            array $data = []
      ): string {

            $ch = curl_init();

            curl_setopt_array($ch, [
                  CURLOPT_URL => $url,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_POST => true,
                  CURLOPT_POSTFIELDS => http_build_query($data),
                  CURLOPT_HTTPHEADER => $headers,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                  throw new RuntimeException(
                        "É necessário que insira os cookies do portal provedor, para continuar o acesso a esta página: " . curl_error($ch)
                  );
            }

            unset($ch);

            return $response;
      }
}
