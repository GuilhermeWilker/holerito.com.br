<?php

declare(strict_types=1);

namespace Holerito\controllers;

use Holerito\integrations\PortalRH\PortalRHCookies;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends Controller
{
      private const COOKIE_KEYS = [
            'RequestVerificationToken',
            'ASPNET_SessionId',
            'ASPXAUTH',
      ];

      public function index(Request $request, Response $response, array $args): Response
      {
            $html = $this->render('login', [
                  'error' => $_SESSION['auth_error'] ?? null,
            ]);

            unset($_SESSION['auth_error']);

            $response->getBody()->write($html);

            return $response;
      }

      public function store(Request $request, Response $response, array $args): Response
      {
            $body = $request->getParsedBody();

            foreach (self::COOKIE_KEYS as $key) {
                  $value = trim($body[$key] ?? '');

                  if ($value === '') {
                        $_SESSION['auth_error'] = "O campo {$key} não pode estar vazio.";

                        return $response
                              ->withHeader('Location', '/auth')
                              ->withStatus(302);
                  }

                  $_SESSION[$key] = $value;
            }

            return $response
                  ->withHeader('Location', '/')
                  ->withStatus(302);
      }

      public function destroy(Request $request, Response $response, array $args): Response
      {
            foreach (self::COOKIE_KEYS as $key) {
                  unset($_SESSION[$key]);
            }

            return $response
                  ->withHeader('Location', '/auth')
                  ->withStatus(302);
      }

      public static function cookiesFromSession(): ?PortalRHCookies
      {
            $keys = self::COOKIE_KEYS;

            foreach ($keys as $key) {
                  if (empty($_SESSION[$key])) {
                        return null;
                  }
            }

            return new PortalRHCookies(
                  $_SESSION['RequestVerificationToken'],
                  $_SESSION['ASPNET_SessionId'],
                  $_SESSION['ASPXAUTH'],
            );
      }
}
