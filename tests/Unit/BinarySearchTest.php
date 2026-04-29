<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Cache\BinarySearch;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;

/**
 * @param  int[]  $ids
 * @return Quote[]
 */
function quotesFromIds(array $ids): array
{
    return array_map(
        fn (int $id) => new Quote(id: $id, quote: "q{$id}", author: "a{$id}"),
        $ids
    );
}

it('returns null on an empty array', function () {
    $bs = new BinarySearch();

    expect($bs->find([], 1))->toBeNull();
});

it('returns null when the id is missing', function () {
    $bs = new BinarySearch();
    $sorted = quotesFromIds([1, 5, 9, 13]);

    expect($bs->find($sorted, 7))->toBeNull();
});

it('finds the first element', function () {
    $bs = new BinarySearch();
    $sorted = quotesFromIds([1, 2, 3, 4]);

    expect($bs->find($sorted, 1))->toBe(0);
});

it('finds the last element', function () {
    $bs = new BinarySearch();
    $sorted = quotesFromIds([1, 2, 3, 4]);

    expect($bs->find($sorted, 4))->toBe(3);
});

it('finds an element in the middle', function () {
    $bs = new BinarySearch();
    $sorted = quotesFromIds([1, 5, 9, 13, 17, 21, 25]);

    expect($bs->find($sorted, 13))->toBe(3);
});

it('runs in O(log n): never exceeds ceil(log2(n)) + 1 comparisons', function () {
    $bs = new BinarySearch();
    $n = 1024; // 2^10
    $sorted = quotesFromIds(range(1, $n));
    $bound = (int) ceil(log($n, 2)) + 1;

    // Worst case existing element (last)
    $bs->find($sorted, $n);
    $whenFound = $bs->lastComparisons;

    // Worst case missing element
    $bs->find($sorted, $n + 1);
    $whenMissing = $bs->lastComparisons;

    expect($whenFound)->toBeLessThanOrEqual($bound)
        ->and($whenMissing)->toBeLessThanOrEqual($bound)
        ->and($whenFound)->toBeLessThan((int) ($n / 10))
        ->and($whenMissing)->toBeLessThan((int) ($n / 10));
});

it('returns the correct insertion point', function () {
    $bs = new BinarySearch();
    $sorted = quotesFromIds([1, 5, 9, 13]);

    expect($bs->insertionPointFor($sorted, 0))->toBe(0)
        ->and($bs->insertionPointFor($sorted, 3))->toBe(1)
        ->and($bs->insertionPointFor($sorted, 14))->toBe(4)
        ->and($bs->insertionPointFor([], 7))->toBe(0)
        ->and($bs->insertionPointFor($sorted, 5))->toBe(1);
});
