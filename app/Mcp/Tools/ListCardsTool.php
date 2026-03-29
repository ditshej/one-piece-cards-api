<?php

namespace App\Mcp\Tools;

use App\Models\Card;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List One Piece TCG cards with optional filters: color, category, cost, pack_id, search.')]
class ListCardsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $cards = Card::query()
            ->when($request->get('color'), fn ($q, $color) => $q->whereJsonContains('colors', $color))
            ->when($request->get('category'), fn ($q, $category) => $q->where('category', $category))
            ->when($request->get('cost'), fn ($q, $cost) => $q->where('cost', $cost))
            ->when($request->get('pack_id'), fn ($q, $pack) => $q->where('pack_id', $pack))
            ->when($request->get('search'), fn ($q, $search) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%{$search}%")
                    ->orWhere('trigger', 'LIKE', "%{$search}%")
            ))
            ->get();

        return Response::json($cards->toArray());
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'color' => $schema->string()->nullable()->description('Filter by color, e.g. "Red"'),
            'category' => $schema->string()->nullable()->description('Filter by category, e.g. "Character"'),
            'cost' => $schema->integer()->nullable()->description('Filter by cost value'),
            'pack_id' => $schema->string()->nullable()->description('Filter by pack ID, e.g. "OP-01"'),
            'search' => $schema->string()->nullable()->description('Search in effect and trigger text'),
        ];
    }
}
