<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Tests\Doubles;

use AntonioVila\QuotesBridge\Exceptions\RateLimitExceededException;

class RateLimitOnceQuotesClient extends FakeQuotesClient
{
    public bool $rateLimitFired = false;

    public function getPage(int $limit, int $skip): array
    {
        if (! $this->rateLimitFired) {
            $this->rateLimitFired = true;

            throw new RateLimitExceededException(
                retryAfter: 1,
                maxRequests: 1,
                windowSeconds: 60,
            );
        }

        return parent::getPage($limit, $skip);
    }
}
