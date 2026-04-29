<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Saloon\DummyJsonConnector;
use AntonioVila\QuotesBridge\Saloon\Requests\GetAllQuotesRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('targets /quotes and includes limit and skip in the query', function () {
    $mock = new MockClient([
        GetAllQuotesRequest::class => MockResponse::make([
            'quotes' => [],
            'total' => 0,
            'skip' => 10,
            'limit' => 50,
        ], 200),
    ]);

    $connector = new DummyJsonConnector('https://dummyjson.com');
    $connector->withMockClient($mock);

    $response = $connector->send(new GetAllQuotesRequest(limit: 50, skip: 10));

    $pending = $response->getPendingRequest();

    expect($pending->getUrl())->toContain('/quotes')
        ->and($pending->query()->all())->toMatchArray([
            'limit' => 50,
            'skip' => 10,
        ]);
});

it('uses sensible defaults when no arguments are passed', function () {
    $request = new GetAllQuotesRequest();

    expect($request->limit)->toBe(100)
        ->and($request->skip)->toBe(0)
        ->and($request->resolveEndpoint())->toBe('/quotes');
});
