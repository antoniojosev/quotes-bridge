<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Cache\QuotesCacheStore;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use AntonioVila\QuotesBridge\Services\QuotesService;
use AntonioVila\QuotesBridge\Tests\Doubles\FakeQuotesClient;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

/**
 * @return array{0: QuotesService, 1: QuotesCacheStore}
 */
function makeService(FakeQuotesClient $client): array
{
    $store = new QuotesCacheStore(
        cache: new Repository(new ArrayStore()),
        key: 'test:service_quotes',
        ttl: 3600,
    );

    return [new QuotesService($client, $store), $store];
}

it('getAll fetches from the client only when the cache is empty', function () {
    $client = new FakeQuotesClient(quotes: [
        new Quote(1, 'a', 'A'),
        new Quote(2, 'b', 'B'),
    ]);
    [$service] = makeService($client);

    $first = $service->getAll();
    $second = $service->getAll();

    expect($first)->toHaveCount(2)
        ->and($second)->toHaveCount(2)
        ->and($client->getAllCalls)->toBe(1);
});

it('getAll returns quotes sorted by id with sequential indexes', function () {
    $client = new FakeQuotesClient(quotes: [
        new Quote(5, 'e', 'E'),
        new Quote(1, 'a', 'A'),
        new Quote(3, 'c', 'C'),
    ]);
    [$service] = makeService($client);

    $all = $service->getAll();

    expect(array_map(fn (Quote $q) => $q->id, $all))->toBe([1, 3, 5])
        ->and(array_keys($all))->toBe([0, 1, 2]);
});

it('getById hits the cache when the quote is already there', function () {
    $client = new FakeQuotesClient(byId: [7 => new Quote(7, 'q', 'a')]);
    [$service] = makeService($client);

    $first = $service->getById(7);
    $second = $service->getById(7);

    expect($first?->id)->toBe(7)
        ->and($second?->id)->toBe(7)
        ->and($client->getByIdCalls)->toBe(1);
});

it('getById fetches from the client and caches the result on miss', function () {
    $client = new FakeQuotesClient(byId: [9 => new Quote(9, 'q', 'a')]);
    [$service, $store] = makeService($client);

    expect($store->find(9))->toBeNull();

    $service->getById(9);

    expect($store->find(9))->not->toBeNull()
        ->and($client->getByIdCalls)->toBe(1);
});

it('getById returns null when neither cache nor client has the quote', function () {
    $client = new FakeQuotesClient();
    [$service] = makeService($client);

    expect($service->getById(99))->toBeNull();
});
