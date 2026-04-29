<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Saloon\DummyJsonConnector;
use AntonioVila\QuotesBridge\Saloon\Requests\GetQuoteByIdRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('builds the /quotes/{id} endpoint with the given id', function () {
    $request = new GetQuoteByIdRequest(7);

    expect($request->resolveEndpoint())->toBe('/quotes/7')
        ->and($request->id)->toBe(7);
});

it('issues the correct URL when sent through the connector', function () {
    $mock = new MockClient([
        GetQuoteByIdRequest::class => MockResponse::make([
            'id' => 5,
            'quote' => 'q',
            'author' => 'a',
        ], 200),
    ]);

    $connector = new DummyJsonConnector('https://dummyjson.com');
    $connector->withMockClient($mock);

    $response = $connector->send(new GetQuoteByIdRequest(5));

    expect($response->getPendingRequest()->getUrl())->toContain('/quotes/5');
});
