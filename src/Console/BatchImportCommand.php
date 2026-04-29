<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Console;

use AntonioVila\QuotesBridge\Cache\QuotesCacheStore;
use AntonioVila\QuotesBridge\Contracts\QuotesClient;
use AntonioVila\QuotesBridge\Exceptions\RateLimitExceededException;
use AntonioVila\QuotesBridge\Support\Sleeper;
use Illuminate\Console\Command;

class BatchImportCommand extends Command
{
    protected $signature = 'quotes:batch-import {count : Number of unique quotes to import.} {--page-size=30}';

    protected $description = 'Import unique quotes into the local cache, retrying on rate-limit hits.';

    public function __construct(
        private readonly QuotesClient $client,
        private readonly QuotesCacheStore $cache,
        private readonly Sleeper $sleeper,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = max(0, (int) $this->argument('count'));
        $pageSize = max(1, (int) $this->option('page-size'));
        $skip = 0;

        if ($target === 0) {
            $this->info('Nothing to import.');

            return self::SUCCESS;
        }

        while (count($this->cache->all()) < $target) {
            try {
                $page = $this->client->getPage($pageSize, $skip);
            } catch (RateLimitExceededException $e) {
                $this->warn("Rate limit hit. Sleeping {$e->retryAfter}s before retrying.");
                $this->sleeper->sleep($e->retryAfter);

                continue;
            }

            if ($page === []) {
                $cached = count($this->cache->all());
                $this->warn("API exhausted at {$cached} quotes (target: {$target}).");
                break;
            }

            foreach ($page as $quote) {
                $this->cache->insert($quote);
                if (count($this->cache->all()) >= $target) {
                    break;
                }
            }

            $skip += $pageSize;
        }

        $cached = count($this->cache->all());
        $this->info("Imported. Cache holds {$cached} unique quotes.");

        return self::SUCCESS;
    }
}
