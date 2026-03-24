<?php

namespace App\Http\Controllers;

use App\Http\Resources\CardResource;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Card::query()
            ->when($request->query('color'), fn ($q, $color) => $q->whereJsonContains('colors', $color))
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->when($request->query('cost'), fn ($q, $cost) => $q->where('cost', $cost))
            ->when($request->query('pack'), fn ($q, $pack) => $q->where('pack_id', $pack))
            ->when($request->query('search'), fn ($q, $search) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%{$search}%")
                    ->orWhere('trigger', 'LIKE', "%{$search}%")
            ));

        return CardResource::collection($query->paginate());
    }

    public function show(Card $card): CardResource
    {
        return new CardResource($card);
    }
}
