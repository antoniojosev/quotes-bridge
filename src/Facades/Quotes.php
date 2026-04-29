<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Facades;

use AntonioVila\QuotesBridge\Saloon\Dto\Quote as QuoteDto;
use AntonioVila\QuotesBridge\Services\QuotesService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static QuoteDto[] getAll()
 * @method static QuoteDto|null getById(int $id)
 *
 * @see QuotesService
 */
class Quotes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QuotesService::class;
    }
}
