<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use AntonioVila\QuotesBridge\Tests\Doubles\FakeQuotesClient;

beforeEach(function () {
    $fake = new FakeQuotesClient(
        quotes: [
            new Quote(1, 'one', 'A'),
            new Quote(2, 'two', 'B'),
            new Quote(3, 'three', 'C'),
        ],
        byId: [
            1 => new Quote(1, 'one', 'A'),
            2 => new Quote(2, 'two', 'B'),
            3 => new Quote(3, 'three', 'C'),
        ],
    );

    $this->app->instance(QuotesClient::class, $fake);
});

it('GET /api/quotes returns paginated quotes', function () {
    $this->get('/api/quotes?per_page=2&page=1')
        ->assertOk()
        ->assertJson([
            'data' => [
                ['id' => 1, 'quote' => 'one', 'author' => 'A'],
                ['id' => 2, 'quote' => 'two', 'author' => 'B'],
            ],
            'meta' => ['page' => 1, 'per_page' => 2, 'total' => 3],
        ]);
});

it('GET /api/quotes second page returns the remainder', function () {
    $this->get('/api/quotes?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', 3);
});

it('GET /api/quotes/{id} returns a single quote', function () {
    $this->get('/api/quotes/2')
        ->assertOk()
        ->assertJson([
            'data' => ['id' => 2, 'quote' => 'two', 'author' => 'B'],
        ]);
});

it('GET /api/quotes/{id} returns 404 when missing', function () {
    $this->get('/api/quotes/999')
        ->assertNotFound();
});
