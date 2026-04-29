<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Support;

interface Sleeper
{
    public function sleep(int $seconds): void;
}
