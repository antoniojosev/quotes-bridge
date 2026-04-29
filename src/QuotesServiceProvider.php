<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge;

use Illuminate\Support\ServiceProvider;

class QuotesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/quotes.php', 'quotes');
    }

    public function boot(): void
    {
        // Routes, views, publishes, and console commands are wired in Phase 3.
    }
}
