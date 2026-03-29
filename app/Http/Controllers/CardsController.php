<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardsIndexRequest;
use App\Http\Resources\CardResource;
use App\Models\Card;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardsController extends Controller
{
    public function index(CardsIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        // The API uses 'pack' as the query parameter; the scope expects 'pack_label'.
        $filters['pack_label'] = $filters['pack'] ?? null;
        unset($filters['pack']);

        $cards = Card::query()
            ->applyFilters($filters)
            ->paginate($filters['per_page'] ?? 15);

        return CardResource::collection($cards);
    }

    public function show(Card $card): CardResource
    {
        return new CardResource($card);
    }
}
