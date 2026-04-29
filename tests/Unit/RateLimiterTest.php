<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Exceptions\RateLimitExceededException;
use AntonioVila\QuotesBridge\Services\RateLimiter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function makeLimiter(int $max = 3, int $window = 60): RateLimiter
{
    $cache = new Repository(new ArrayStore());

    return new RateLimiter(
        cache: $cache,
        cacheKey: 'test:rate_limit',
        maxRequests: $max,
        windowSeconds: $window,
    );
}

it('allows requests under the limit without throwing', function () {
    $limiter = makeLimiter(max: 3);

    $limiter->attempt();
    $limiter->attempt();
    $limiter->attempt();

    expect($limiter->hits())->toBe(3)
        ->and($limiter->remaining())->toBe(0);
});

it('throws RateLimitExceededException when the limit is reached', function () {
    $limiter = makeLimiter(max: 2);

    $limiter->attempt();
    $limiter->attempt();
    $limiter->attempt();
})->throws(RateLimitExceededException::class);

it('exposes retryAfter, maxRequests, and windowSeconds on the exception', function () {
    $limiter = makeLimiter(max: 1, window: 60);

    $limiter->attempt();

    try {
        $limiter->attempt();
        $this->fail('Expected RateLimitExceededException to be thrown.');
    } catch (RateLimitExceededException $e) {
        expect($e->retryAfter)->toBeGreaterThan(0)
            ->and($e->retryAfter)->toBeLessThanOrEqual(60)
            ->and($e->maxRequests)->toBe(1)
            ->and($e->windowSeconds)->toBe(60);
    }
});

it('clears its state when reset() is called', function () {
    $limiter = makeLimiter(max: 1, window: 60);

    $limiter->attempt();
    expect($limiter->hits())->toBe(1);

    $limiter->reset();

    expect($limiter->hits())->toBe(0);

    $limiter->attempt();
    expect($limiter->hits())->toBe(1);
});

it('does not block: attempts that exceed the limit fail fast', function () {
    $limiter = makeLimiter(max: 1, window: 60);

    $limiter->attempt();

    $start = microtime(true);
    try {
        $limiter->attempt();
    } catch (RateLimitExceededException) {
        // expected
    }
    $elapsed = microtime(true) - $start;

    expect($elapsed)->toBeLessThan(0.1);
});
