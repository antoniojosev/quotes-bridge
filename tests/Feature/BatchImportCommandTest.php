<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Cache\QuotesCacheStore;
use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use AntonioVila\QuotesBridge\Support\Sleeper;
use AntonioVila\QuotesBridge\Tests\Doubles\FakeQuotesClient;
use AntonioVila\QuotesBridge\Tests\Doubles\FakeSleeper;
use AntonioVila\QuotesBridge\Tests\Doubles\RateLimitOnceQuotesClient;

it('imports the requested number of unique quotes', function () {
    $fake = new FakeQuotesClient(quotes: array_map(
        fn (int $id) => new Quote($id, "q{$id}", "a{$id}"),
        range(1, 50),
    ));
    $sleeper = new FakeSleeper();

    $this->app->instance(QuotesClient::class, $fake);
    $this->app->instance(Sleeper::class, $sleeper);

    $this->artisan('quotes:batch-import', ['count' => 25, '--page-size' => 10])
        ->assertExitCode(0);

    $store = $this->app->make(QuotesCacheStore::class);

    expect(count($store->all()))->toBeGreaterThanOrEqual(25)
        ->and($sleeper->sleeps)->toBe([]);
});

it('retries after a RateLimitExceededException and records the sleep', function () {
    $client = new RateLimitOnceQuotesClient(quotes: array_map(
        fn (int $id) => new Quote($id, "q{$id}", "a{$id}"),
        range(1, 30),
    ));
    $sleeper = new FakeSleeper();

    $this->app->instance(QuotesClient::class, $client);
    $this->app->instance(Sleeper::class, $sleeper);

    $this->artisan('quotes:batch-import', ['count' => 10, '--page-size' => 10])
        ->assertExitCode(0);

    expect($client->rateLimitFired)->toBeTrue()
        ->and($sleeper->sleeps)->toContain(1);
});

it('stops gracefully when the API runs out of quotes', function () {
    $fake = new FakeQuotesClient(quotes: [
        new Quote(1, 'a', 'A'),
        new Quote(2, 'b', 'B'),
    ]);
    $sleeper = new FakeSleeper();

    $this->app->instance(QuotesClient::class, $fake);
    $this->app->instance(Sleeper::class, $sleeper);

    $this->artisan('quotes:batch-import', ['count' => 100, '--page-size' => 10])
        ->assertExitCode(0);

    $store = $this->app->make(QuotesCacheStore::class);

    expect($store->all())->toHaveCount(2);
});
