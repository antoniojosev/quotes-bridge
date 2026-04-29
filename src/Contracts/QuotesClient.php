<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Contracts;

use AntonioVila\QuotesBridge\Saloon\Dto\Quote;

interface QuotesClient
{
    /**
     * @return Quote[]
     */
    public function getAll(): array;

    public function getById(int $id): ?Quote;

    /**
     * @return Quote[]
     */
    public function getPage(int $limit, int $skip): array;
}
