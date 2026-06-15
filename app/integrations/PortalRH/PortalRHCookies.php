<?php

declare(strict_types=1);

namespace Holerito\integrations\PortalRH;

final class PortalRHCookies
{
      public function __construct(
            private readonly string $RequestVerificationToken,
            private readonly string $ASPNET_SessionId,
            private readonly string $ASPXAUTH
      ) {}

      public function get()
      {
            return "__RequestVerificationToken_{$this->RequestVerificationToken}; ASP.NET_SessionId={$this->ASPNET_SessionId}; .ASPXAUTH={$this->ASPXAUTH}";
      }
}
