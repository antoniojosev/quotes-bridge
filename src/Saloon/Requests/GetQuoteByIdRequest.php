<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Saloon\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetQuoteByIdRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(public readonly int $id)
    {
    }

    public function resolveEndpoint(): string
    {
        return "/quotes/{$this->id}";
    }
}
