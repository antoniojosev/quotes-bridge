<?php

declare(strict_types=1);

namespace AntonioVila\QuotesBridge\Http\Controllers;

use AntonioVila\QuotesBridge\Saloon\Dto\Quote;
use AntonioVila\QuotesBridge\Services\QuotesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotesController
{
    public function __construct(private readonly QuotesService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $defaultPerPage = (int) config('quotes.pagination.per_page', 20);
        $perPage = max(1, min(100, (int) $request->query('per_page', $defaultPerPage)));
        $page = max(1, (int) $request->query('page', 1));

        $all = $this->service->getAll();
        $total = count($all);
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($all, $offset, $perPage);

        return new JsonResponse([
            'data' => array_map(fn (Quote $q) => $q->toArray(), $slice),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $quote = $this->service->getById($id);

        if ($quote === null) {
            return new JsonResponse(['message' => 'Quote not found.'], 404);
        }

        return new JsonResponse(['data' => $quote->toArray()]);
    }
}
