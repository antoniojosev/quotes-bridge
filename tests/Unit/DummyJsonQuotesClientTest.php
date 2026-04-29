<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Exceptions\RateLimitExceededException;
use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use AntonioVila\QuotesBridge\Saloon\DummyJsonConnector;
use AntonioVila\QuotesBridge\Saloon\Requests\GetAllQuotesRequest;
use AntonioVila\QuotesBridge\Saloon\Requests\GetQuoteByIdRequest;
use AntonioVila\QuotesBridge\Services\DummyJsonQuotesClient;
use AntonioVila\QuotesBridge\Services\RateLimiter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function makeJsonClient(MockClient $mock, int $rateMax = 1000): DummyJsonQuotesClient
{
    $cache = new Repository(new ArrayStore());
    $connector = new DummyJsonConnector('https://dummyjson.com');
    $connector->withMockClient($mock);

    $limiter = new RateLimiter(
        cache: $cache,
        cacheKey: 'test:client_rate_limit',
        maxRequests: $rateMax,
        windowSeconds: 60,
    );

    return new DummyJsonQuotesClient($connector, $limiter);
}

it('getAll returns Quote DTOs parsed from the API response', function () {
    $mock = new MockClient([
        GetAllQuotesRequest::class => MockResponse::make([
            'quotes' => [
                ['id' => 1, 'quote' => 'a', 'author' => 'A'],
                ['id' => 2, 'quote' => 'b', 'author' => 'B'],
            ],
            'total' => 2,
            'skip' => 0,
            'limit' => 100,
        ]),
    ]);

    $client = makeJsonClient($mock);
    $quotes = $client->getAll();

    expect($quotes)->toHaveCount(2)
        ->and($quotes[0])->toBeInstanceOf(Quote::class)
        ->and($quotes[0]->id)->toBe(1)
        ->and($quotes[1]->author)->toBe('B');
});

it('getById returns a Quote when the API responds 200', function () {
    $mock = new MockClient([
        GetQuoteByIdRequest::class => MockResponse::make([
            'id' => 7,
            'quote' => 'be water',
            'author' => 'Bruce Lee',
        ]),
    ]);

    $client = makeJsonClient($mock);
    $quote = $client->getById(7);

    expect($quote)->not->toBeNull()
        ->and($quote->id)->toBe(7)
        ->and($quote->quote)->toBe('be water');
});

it('getById returns null when the API responds 404', function () {
    $mock = new MockClient([
        GetQuoteByIdRequest::class => MockResponse::make(['message' => 'not found'], 404),
    ]);

    $client = makeJsonClient($mock);

    expect($client->getById(999999))->toBeNull();
});

it('propagates RateLimitExceededException when the limiter is full', function () {
    $mock = new MockClient([
        GetQuoteByIdRequest::class => MockResponse::make([
            'id' => 1,
            'quote' => 'x',
            'author' => 'y',
        ]),
    ]);

    $client = makeJsonClient($mock, rateMax: 1);

    $client->getById(1);
    $client->getById(2);
})->throws(RateLimitExceededException::class);
