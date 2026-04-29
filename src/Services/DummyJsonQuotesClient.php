<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Services;

use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use AntonioVila\QuotesBridge\Saloon\DummyJsonConnector;
use AntonioVila\QuotesBridge\Saloon\Requests\GetAllQuotesRequest;
use AntonioVila\QuotesBridge\Saloon\Requests\GetQuoteByIdRequest;

class DummyJsonQuotesClient implements QuotesClient
{
    public function __construct(
        private readonly DummyJsonConnector $connector,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    /**
     * @return Quote[]
     */
    public function getAll(): array
    {
        return $this->getPage(limit: 100, skip: 0);
    }

    public function getById(int $id): ?Quote
    {
        $this->rateLimiter->attempt();

        $response = $this->connector->send(new GetQuoteByIdRequest($id));

        if ($response->status() === 404) {
            return null;
        }

        return Quote::fromArray($response->json());
    }

    /**
     * @return Quote[]
     */
    public function getPage(int $limit, int $skip): array
    {
        $this->rateLimiter->attempt();

        $response = $this->connector->send(new GetAllQuotesRequest(limit: $limit, skip: $skip));
        $body = $response->json();

        return array_map(
            fn (array $q) => Quote::fromArray($q),
            $body['quotes'] ?? []
        );
    }
}
