<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Exceptions;

use RuntimeException;

class RateLimitExceededException extends RuntimeException
{
    public function __construct(
        public readonly int $retryAfter,
        public readonly int $maxRequests,
        public readonly int $windowSeconds,
    ) {
        parent::__construct(
            "Rate limit of {$maxRequests} requests per {$windowSeconds}s exceeded. Retry after {$retryAfter}s."
        );
    }
}
