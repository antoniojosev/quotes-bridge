<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Cache;

use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class QuotesCacheStore
{
    private BinarySearch $bs;

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $key,
        private readonly int $ttl,
    ) {
        $this->bs = new BinarySearch();
    }

    /**
     * @return Quote[]
     */
    public function all(): array
    {
        $payload = $this->cache->get($this->key);
        if ($payload === null) {
            return [];
        }

        return array_map(
            fn (array $q) => Quote::fromArray($q),
            $payload['quotes'] ?? []
        );
    }

    public function isHydrated(): bool
    {
        $payload = $this->cache->get($this->key);

        return ($payload['is_hydrated'] ?? false) === true;
    }

    public function find(int $id): ?Quote
    {
        $sorted = $this->all();
        $idx = $this->bs->find($sorted, $id);

        return $idx === null ? null : $sorted[$idx];
    }

    public function insert(Quote $quote): void
    {
        $sorted = $this->all();
        $hydrated = $this->isHydrated();
        $pos = $this->bs->insertionPointFor($sorted, $quote->id);

        if (isset($sorted[$pos]) && $sorted[$pos]->id === $quote->id) {
            $sorted[$pos] = $quote;
        } else {
            array_splice($sorted, $pos, 0, [$quote]);
        }

        $this->persist($sorted, $hydrated);
    }

    public function markHydrated(): void
    {
        $this->persist($this->all(), true);
    }

    public function flush(): void
    {
        $this->cache->forget($this->key);
    }

    /**
     * @param  Quote[]  $sorted
     */
    private function persist(array $sorted, bool $hydrated): void
    {
        $this->cache->put($this->key, [
            'quotes' => array_map(fn (Quote $q) => $q->toArray(), $sorted),
            'is_hydrated' => $hydrated,
        ], $this->ttl);
    }
}
