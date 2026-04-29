<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Saloon\DummyJsonConnector;

it('resolves the base url it was constructed with', function () {
    $connector = new DummyJsonConnector('https://example.test/api');

    expect($connector->resolveBaseUrl())->toBe('https://example.test/api');
});
