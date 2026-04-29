<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Cache\QuotesCacheStore;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;

function makeStore(): QuotesCacheStore
{
    $cache = new Repository(new ArrayStore());

    return new QuotesCacheStore(
        cache: $cache,
        key: 'test:quotes_store',
        ttl: 3600,
    );
}

it('starts empty and not hydrated', function () {
    $store = makeStore();

    expect($store->all())->toBe([])
        ->and($store->find(1))->toBeNull()
        ->and($store->isHydrated())->toBeFalse();
});

it('inserts and retrieves a quote', function () {
    $store = makeStore();
    $store->insert(new Quote(1, 'hello', 'someone'));

    $found = $store->find(1);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe(1)
        ->and($found->quote)->toBe('hello');
});

it('keeps the array sorted by id when inserting out of order', function () {
    $store = makeStore();

    foreach ([5, 1, 9, 3] as $id) {
        $store->insert(new Quote($id, "q{$id}", 'a'));
    }

    $ids = array_map(fn (Quote $q) => $q->id, $store->all());

    expect($ids)->toBe([1, 3, 5, 9]);
});

it('keeps array indexes sequential 0..N-1', function () {
    $store = makeStore();

    foreach ([5, 1, 9, 3] as $id) {
        $store->insert(new Quote($id, "q{$id}", 'a'));
    }

    expect(array_keys($store->all()))->toBe([0, 1, 2, 3]);
});

it('updates instead of duplicating when inserting an existing id', function () {
    $store = makeStore();

    $store->insert(new Quote(1, 'first', 'a'));
    $store->insert(new Quote(1, 'updated', 'a'));

    expect($store->all())->toHaveCount(1)
        ->and($store->find(1)?->quote)->toBe('updated');
});

it('persists with is_hydrated false after a partial insert', function () {
    $cache = new Repository(new ArrayStore());
    $store = new QuotesCacheStore(
        cache: $cache,
        key: 'test:quotes_store',
        ttl: 3600,
    );

    $store->insert(new Quote(1, 'hi', 'a'));

    $raw = $cache->get('test:quotes_store');

    expect($raw)->toBeArray()
        ->and($raw)->toHaveKey('is_hydrated')
        ->and($raw['is_hydrated'])->toBeFalse();
});

it('flips is_hydrated to true on markHydrated() and preserves it through inserts', function () {
    $store = makeStore();
    $store->insert(new Quote(1, 'a', 'A'));
    $store->insert(new Quote(2, 'b', 'B'));

    expect($store->isHydrated())->toBeFalse();

    $store->markHydrated();

    expect($store->isHydrated())->toBeTrue();

    $store->insert(new Quote(3, 'c', 'C'));

    expect($store->isHydrated())->toBeTrue();
});

it('clears all state on flush()', function () {
    $store = makeStore();
    $store->insert(new Quote(1, 'hi', 'a'));
    $store->markHydrated();

    $store->flush();

    expect($store->all())->toBe([])
        ->and($store->isHydrated())->toBeFalse();
});
