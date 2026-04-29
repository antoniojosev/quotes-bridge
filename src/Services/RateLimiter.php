<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Services;

use AntonioVila\QuotesBridge\Exceptions\RateLimitExceededException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class RateLimiter
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $cacheKey,
        private readonly int $maxRequests,
        private readonly int $windowSeconds,
    ) {
    }

    public function attempt(): void
    {
        $current = $this->hits();

        if ($current >= $this->maxRequests) {
            throw new RateLimitExceededException(
                retryAfter: $this->retryAfter(),
                maxRequests: $this->maxRequests,
                windowSeconds: $this->windowSeconds,
            );
        }

        if ($current === 0) {
            $this->cache->put($this->cacheKey, 1, $this->windowSeconds);
            $this->cache->put(
                $this->resetKey(),
                time() + $this->windowSeconds,
                $this->windowSeconds,
            );
            return;
        }

        $this->cache->increment($this->cacheKey);
    }

    public function hits(): int
    {
        return (int) $this->cache->get($this->cacheKey, 0);
    }

    public function remaining(): int
    {
        return max(0, $this->maxRequests - $this->hits());
    }

    public function retryAfter(): int
    {
        $resetAt = (int) $this->cache->get($this->resetKey(), 0);
        if ($resetAt === 0) {
            return $this->windowSeconds;
        }
        return max(0, $resetAt - time());
    }

    public function reset(): void
    {
        $this->cache->forget($this->cacheKey);
        $this->cache->forget($this->resetKey());
    }

    private function resetKey(): string
    {
        return $this->cacheKey . ':reset_at';
    }
}
