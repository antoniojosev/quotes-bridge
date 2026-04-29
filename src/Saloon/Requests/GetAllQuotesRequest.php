<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Saloon\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAllQuotesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly int $limit = 100,
        public readonly int $skip = 0,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/quotes';
    }

    protected function defaultQuery(): array
    {
        return [
            'limit' => $this->limit,
            'skip' => $this->skip,
        ];
    }
}
