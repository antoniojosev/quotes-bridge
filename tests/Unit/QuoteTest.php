<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Saloon\Dto\Quote;

it('builds a Quote from an associative array', function () {
    $quote = Quote::fromArray([
        'id' => 7,
        'quote' => 'Stay hungry, stay foolish.',
        'author' => 'Steve Jobs',
    ]);

    expect($quote->id)->toBe(7)
        ->and($quote->quote)->toBe('Stay hungry, stay foolish.')
        ->and($quote->author)->toBe('Steve Jobs');
});

it('round-trips through toArray()', function () {
    $original = new Quote(1, 'hello', 'someone');

    expect(Quote::fromArray($original->toArray()))->toEqual($original);
});

it('coerces a numeric string id into int', function () {
    $quote = Quote::fromArray([
        'id' => '42',
        'quote' => 'x',
        'author' => 'y',
    ]);

    expect($quote->id)->toBe(42);
});
