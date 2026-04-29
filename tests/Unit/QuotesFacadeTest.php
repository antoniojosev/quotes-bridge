<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Facades\Quotes;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use AntonioVila\QuotesBridge\Services\QuotesService;
use AntonioVila\QuotesBridge\Tests\Doubles\FakeQuotesClient;

it('resolves the QuotesService binding from the container', function () {
    expect(app(QuotesService::class))->toBeInstanceOf(QuotesService::class);
});

it('routes static getById through the underlying service', function () {
    $fake = new FakeQuotesClient(byId: [
        7 => new Quote(7, 'be water', 'Bruce Lee'),
    ]);
    $this->app->instance(QuotesClient::class, $fake);

    $quote = Quotes::getById(7);

    expect($quote)->not->toBeNull()
        ->and($quote->id)->toBe(7)
        ->and($quote->author)->toBe('Bruce Lee');
});

it('routes static getAll through the underlying service', function () {
    $fake = new FakeQuotesClient(quotes: [
        new Quote(1, 'a', 'A'),
        new Quote(2, 'b', 'B'),
    ]);
    $this->app->instance(QuotesClient::class, $fake);

    $all = Quotes::getAll();

    expect($all)->toHaveCount(2)
        ->and($all[0]->id)->toBe(1);
});
