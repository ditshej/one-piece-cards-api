<?php

namespace App\Mcp\Tools;

use App\Models\Card;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List One Piece TCG cards with optional filters. Defaults to 20 results — use limit to get more (max 100).')]
class ListCardsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $cards = Card::query()
            ->applyFilters($request->all())
            ->limit($request->get('limit', 20))
            ->get();

        return Response::json($cards->toArray());
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'color' => $schema->string()->nullable()->description('Filter by color: Red, Blue, Green, Purple, Black, Yellow'),
            'category' => $schema->string()->nullable()->description('Filter by category: Character, Event, Leader, Stage'),
            'cost' => $schema->integer()->nullable()->description('Filter by exact cost value'),
            'cost_min' => $schema->integer()->nullable()->description('Filter by minimum cost'),
            'cost_max' => $schema->integer()->nullable()->description('Filter by maximum cost'),
            'power_min' => $schema->integer()->nullable()->description('Filter by minimum power'),
            'power_max' => $schema->integer()->nullable()->description('Filter by maximum power'),
            'pack_label' => $schema->string()->nullable()->description('Filter by pack label, e.g. "OP-15"'),
            'search' => $schema->string()->nullable()->description('Free-text search in effect and trigger text'),
            'name' => $schema->string()->nullable()->description('Filter by card name (partial match)'),
            'rarity' => $schema->string()->nullable()->description('Filter by rarity: Common, Uncommon, Rare, SuperRare, SecretRare, TreasureRare, Special, Promo, Leader'),
            'attribute' => $schema->string()->nullable()->description('Filter by attribute: Strike, Slash, Ranged, Wisdom, Special'),
            'type' => $schema->string()->nullable()->description('Filter by type/archetype, e.g. "Egghead", "Straw Hat Crew"'),
            'keyword' => $schema->string()->nullable()->description('Filter by TCG keyword, e.g. "Blocker" searches for [Blocker] in effect/trigger'),
            'alt_art' => $schema->boolean()->nullable()->description('Set to true to return only alt art cards'),
            'limit' => $schema->integer()->nullable()->description('Number of results to return (default 20, max 100)'),
        ];
    }
}
