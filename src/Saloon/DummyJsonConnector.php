<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Saloon;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class DummyJsonConnector extends Connector
{
    use AcceptsJson;

    public function __construct(private readonly string $baseUrl)
    {
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
