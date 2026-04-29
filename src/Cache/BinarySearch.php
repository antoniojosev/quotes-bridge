<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Cache;

use AntonioVila\QuotesBridge\Saloon\Dto\Quote;

class BinarySearch
{
    public int $lastComparisons = 0;

    /**
     * Locate the index of the Quote with the given id.
     * Runs in O(log n) over a sorted array of Quote objects.
     *
     * @param  Quote[]  $sorted  Quotes sorted ascending by id.
     */
    public function find(array $sorted, int $id): ?int
    {
        $this->lastComparisons = 0;

        $low = 0;
        $high = count($sorted) - 1;

        while ($low <= $high) {
            $this->lastComparisons++;
            $mid = intdiv($low + $high, 2);
            $candidate = $sorted[$mid]->id;

            if ($candidate === $id) {
                return $mid;
            }

            if ($candidate < $id) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return null;
    }

    /**
     * Index where a Quote with the given id should be inserted to keep the
     * array sorted ascending by id. If a Quote with that id already exists,
     * the returned index points at it.
     *
     * @param  Quote[]  $sorted
     */
    public function insertionPointFor(array $sorted, int $id): int
    {
        $low = 0;
        $high = count($sorted) - 1;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);
            $candidate = $sorted[$mid]->id;

            if ($candidate === $id) {
                return $mid;
            }

            if ($candidate < $id) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return $low;
    }
}
