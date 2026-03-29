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
        $query = Card::query()
            ->when($request->validated('color'), fn ($q, $color) => $q->whereJsonContains('colors', $color))
            ->when($request->validated('category'), fn ($q, $category) => $q->where('category', $category))
            ->when($request->validated('cost'), fn ($q, $cost) => $q->where('cost', $cost))
            ->when($request->validated('pack'), fn ($q, $pack) => $q->whereHas('pack', fn ($r) => $r->where('label', $pack)))
            ->when($request->validated('search'), fn ($q, $search) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%{$search}%")
                    ->orWhere('trigger', 'LIKE', "%{$search}%")
            ))
            ->when($request->validated('name'), fn ($q, $name) => $q->where('name', 'LIKE', "%{$name}%"))
            ->when($request->validated('rarity'), fn ($q, $rarity) => $q->where('rarity', $rarity))
            ->when($request->validated('attribute'), fn ($q, $attribute) => $q->whereJsonContains('attributes', $attribute))
            ->when($request->validated('type'), fn ($q, $type) => $q->whereJsonContains('types', $type))
            ->when($request->validated('cost_min'), fn ($q, $min) => $q->where('cost', '>=', $min))
            ->when($request->validated('cost_max'), fn ($q, $max) => $q->where('cost', '<=', $max))
            ->when($request->validated('power_min'), fn ($q, $min) => $q->where('power', '>=', $min))
            ->when($request->validated('power_max'), fn ($q, $max) => $q->where('power', '<=', $max))
            ->when($request->validated('keyword'), fn ($q, $keyword) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%[{$keyword}]%")
                    ->orWhere('trigger', 'LIKE', "%[{$keyword}]%")
            ))
            ->when($request->validated('alt_art'), fn ($q) => $q->whereRaw("INSTR(id, '_p') > 0"));

        return CardResource::collection($query->paginate($request->validated('per_page', 15)));
    }

    public function show(Card $card): CardResource
    {
        return new CardResource($card);
    }
}
