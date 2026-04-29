<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Services;

use AntonioVila\QuotesBridge\Cache\QuotesCacheStore;
use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;

class QuotesService
{
    public function __construct(
        private readonly QuotesClient $client,
        private readonly QuotesCacheStore $cache,
    ) {
    }

    /**
     * @return Quote[]
     */
    public function getAll(): array
    {
        $cached = $this->cache->all();
        if (count($cached) > 0) {
            return $cached;
        }

        foreach ($this->client->getAll() as $quote) {
            $this->cache->insert($quote);
        }

        return $this->cache->all();
    }

    public function getById(int $id): ?Quote
    {
        $cached = $this->cache->find($id);
        if ($cached !== null) {
            return $cached;
        }

        $fetched = $this->client->getById($id);
        if ($fetched === null) {
            return null;
        }

        $this->cache->insert($fetched);

        return $fetched;
    }
}
