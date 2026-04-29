<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Tests\Doubles;

use AntonioVila\QuotesBridge\Support\Sleeper;

class FakeSleeper implements Sleeper
{
    /** @var int[] */
    public array $sleeps = [];

    public function sleep(int $seconds): void
    {
        $this->sleeps[] = $seconds;
    }
}
