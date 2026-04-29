<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Tests\Doubles;

use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;

class FakeQuotesClient implements QuotesClient
{
    public int $getAllCalls = 0;

    public int $getByIdCalls = 0;

    public int $getPageCalls = 0;

    /**
     * @param  Quote[]  $quotes
     * @param  array<int, Quote|null>  $byId
     */
    public function __construct(
        public array $quotes = [],
        public array $byId = [],
    ) {
    }

    public function getAll(): array
    {
        $this->getAllCalls++;

        return $this->quotes;
    }

    public function getById(int $id): ?Quote
    {
        $this->getByIdCalls++;

        return $this->byId[$id] ?? null;
    }

    public function getPage(int $limit, int $skip): array
    {
        $this->getPageCalls++;

        return array_slice($this->quotes, $skip, $limit);
    }
}
