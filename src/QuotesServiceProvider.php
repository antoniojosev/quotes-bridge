<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge;

use AntonioVila\QuotesBridge\Cache\QuotesCacheStore;
use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Saloon\DummyJsonConnector;
use AntonioVila\QuotesBridge\Services\DummyJsonQuotesClient;
use AntonioVila\QuotesBridge\Services\QuotesService;
use AntonioVila\QuotesBridge\Services\RateLimiter;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;

class QuotesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/quotes.php', 'quotes');

        $this->app->singleton(DummyJsonConnector::class, function ($app) {
            $config = $app->make(ConfigRepository::class);

            return new DummyJsonConnector((string) $config->get('quotes.base_url'));
        });

        $this->app->singleton(RateLimiter::class, function ($app) {
            $config = $app->make(ConfigRepository::class);
            $cache = $app->make(CacheFactory::class);

            return new RateLimiter(
                cache: $cache->store(),
                cacheKey: (string) $config->get('quotes.rate_limit.cache_key'),
                maxRequests: (int) $config->get('quotes.rate_limit.max_requests'),
                windowSeconds: (int) $config->get('quotes.rate_limit.window_seconds'),
            );
        });

        $this->app->singleton(QuotesCacheStore::class, function ($app) {
            $config = $app->make(ConfigRepository::class);
            $cache = $app->make(CacheFactory::class);
            $store = $config->get('quotes.cache.store');

            return new QuotesCacheStore(
                cache: $cache->store($store),
                key: (string) $config->get('quotes.cache.key'),
                ttl: (int) $config->get('quotes.cache.ttl'),
            );
        });

        $this->app->singleton(QuotesClient::class, fn ($app) => new DummyJsonQuotesClient(
            $app->make(DummyJsonConnector::class),
            $app->make(RateLimiter::class),
        ));

        $this->app->singleton(QuotesService::class, fn ($app) => new QuotesService(
            $app->make(QuotesClient::class),
            $app->make(QuotesCacheStore::class),
        ));
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
    }
}
