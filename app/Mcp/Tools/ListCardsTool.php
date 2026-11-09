<?php

namespace App\Mcp\Tools;

use App\Http\Requests\CardsIndexRequest;
use App\Models\Card;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List One Piece TCG cards with optional filters. Defaults to 20 results — use limit to get more (max 100).')]
class ListCardsTool extends Tool
{
    private const ARGUMENT_TO_REST_KEYS = ['pack_label' => 'pack', 'limit' => 'per_page'];

    public function handle(Request $request): Response
    {
        $input = collect(CardsIndexRequest::wrapListFilters($request->all()))
            ->mapWithKeys(fn (mixed $value, string $key) => [self::ARGUMENT_TO_REST_KEYS[$key] ?? $key => $value])
            ->all();

        $validated = Validator::validate($input, CardsIndexRequest::filterRules());

        $cards = Card::query()
            ->applyFilters(self::toScopeFilters($validated))
            ->limit($validated['per_page'] ?? 20)
            ->get();

        return Response::json($cards->toArray());
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        $text = fn (string $description) => $this->oneOrMany($schema, $schema->string(), $description);
        $number = fn (string $description) => $this->oneOrMany($schema, $schema->integer(), $description);

        return [
            'color' => $text('Filter by color (one value or a list, any match): Red, Blue, Green, Purple, Black, Yellow'),
            'color_not' => $text('Exclude cards with any of these colors'),
            'category' => $text('Filter by category (one value or a list): Character, Event, Leader, Stage'),
            'category_not' => $text('Exclude these categories'),
            'cost' => $number('Filter by exact cost (one value or a list)'),
            'cost_min' => $schema->integer()->nullable()->description('Filter by minimum cost'),
            'cost_max' => $schema->integer()->nullable()->description('Filter by maximum cost'),
            'cost_not' => $number('Exclude these costs'),
            'power' => $number('Filter by exact power (one value or a list)'),
            'power_min' => $schema->integer()->nullable()->description('Filter by minimum power'),
            'power_max' => $schema->integer()->nullable()->description('Filter by maximum power'),
            'power_not' => $number('Exclude these power values'),
            'counter' => $number('Filter by exact counter value (one value or a list)'),
            'counter_not' => $number('Exclude these counter values (cards without counter are kept)'),
            'pack_label' => $schema->string()->nullable()->description('Filter by pack label, e.g. "OP-15"'),
            'search' => $schema->string()->nullable()->description('Free-text search in effect and trigger text'),
            'name' => $schema->string()->nullable()->description('Filter by card name (partial match)'),
            'rarity' => $text('Filter by rarity (one value or a list): Common, Uncommon, Rare, SuperRare, SecretRare, TreasureRare, Special, Promo, Leader'),
            'rarity_not' => $text('Exclude these rarities'),
            'attribute' => $text('Filter by attribute (one value or a list): Strike, Slash, Ranged, Wisdom, Special'),
            'attribute_not' => $text('Exclude these attributes'),
            'type' => $text('Filter by type/archetype (one value or a list), e.g. "Egghead", "Straw Hat Crew"'),
            'type_not' => $text('Exclude these types/archetypes'),
            'keyword' => $text('Filter by TCG keyword (one value or a list), e.g. "Blocker" searches for [Blocker] in effect/trigger'),
            'keyword_not' => $text('Exclude cards with these keywords in effect/trigger'),
            'card_set' => $text('Filter by origin set (ID prefix, one value or a list), e.g. "OP03" — returns all cards from that set regardless of which pack they appear in'),
            'card_set_not' => $text('Exclude cards from these origin sets'),
            'has_trigger' => $schema->boolean()->nullable()->description('true: only cards with a trigger, false: only cards without'),
            'has_effect' => $schema->boolean()->nullable()->description('true: only cards with an effect, false: only cards without'),
            'has_counter' => $schema->boolean()->nullable()->description('true: only cards with a counter value, false: only cards without'),
            'alt_art' => $schema->boolean()->nullable()->description('Set to true to return only alt art cards'),
            'limit' => $schema->integer()->min(1)->max(100)->nullable()->description('Number of results to return (default 20, 1 to 100)'),
        ];
    }

    /**
     * @param  array<mixed>  $validated
     * @return array<string, mixed>
     */
    private static function toScopeFilters(array $validated): array
    {
        return collect($validated)
            ->mapWithKeys(fn (mixed $value, string $key) => [array_flip(self::ARGUMENT_TO_REST_KEYS)[$key] ?? $key => $value])
            ->all();
    }

    private function oneOrMany(JsonSchema $schema, Type $type, string $description): Type
    {
        return $schema->anyOf([$type, $schema->array()->items($type)])->description($description);
    }
}
