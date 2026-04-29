<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Saloon\Dto;

final class Quote
{
    public function __construct(
        public readonly int $id,
        public readonly string $quote,
        public readonly string $author,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            quote: (string) $data['quote'],
            author: (string) $data['author'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'quote' => $this->quote,
            'author' => $this->author,
        ];
    }
}
