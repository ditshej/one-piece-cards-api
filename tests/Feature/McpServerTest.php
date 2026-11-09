<?php

use App\Http\Requests\CardsIndexRequest;
use App\Mcp\Servers\CardsServer;
use App\Mcp\Tools\GetCardTool;
use App\Mcp\Tools\GetPackTool;
use App\Mcp\Tools\ListCardsTool;
use App\Mcp\Tools\ListPacksTool;
use App\Models\Card;
use App\Models\Pack;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;

it('list-packs returns all packs', function () {
    Pack::factory()->count(3)->create();

    CardsServer::tool(ListPacksTool::class)
        ->assertOk()
        ->assertSee(Pack::first()->name);
});

it('get-pack returns the pack with its cards', function () {
    $pack = Pack::factory()->create();
    Card::factory()->count(2)->create(['pack_id' => $pack->id]);

    CardsServer::tool(GetPackTool::class, ['pack_id' => $pack->id])
        ->assertOk()
        ->assertSee($pack->name);
});

it('list-cards filters by color', function () {
    $pack = Pack::factory()->create();
    Card::factory()->create(['pack_id' => $pack->id, 'colors' => ['Red']]);
    Card::factory()->create(['pack_id' => $pack->id, 'colors' => ['Blue']]);

    $response = CardsServer::tool(ListCardsTool::class, ['color' => 'Red'])
        ->assertOk();

    $response->assertSee('Red');
    $response->assertDontSee('Blue');
});

it('get-card returns the card', function () {
    $pack = Pack::factory()->create();
    $card = Card::factory()->create(['pack_id' => $pack->id]);

    CardsServer::tool(GetCardTool::class, ['card_id' => $card->id])
        ->assertOk()
        ->assertSee($card->name);
});

it('list-cards schema covers every filter of the REST request', function () {
    $restFilters = collect(array_keys((new CardsIndexRequest)->rules()))
        ->reject(fn (string $key) => str_ends_with($key, '.*'))
        ->map(fn (string $key) => match ($key) {
            'pack' => 'pack_label',
            'per_page' => 'limit',
            default => $key,
        })
        ->sort()
        ->values()
        ->all();

    $schemaKeys = collect(array_keys((new ListCardsTool)->schema(new JsonSchemaTypeFactory)))
        ->sort()
        ->values()
        ->all();

    expect($schemaKeys)->toBe($restFilters);
});

it('list-cards accepts a list of colors', function () {
    $pack = Pack::factory()->create();
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Redcard', 'colors' => ['Red']]);
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Bluecard', 'colors' => ['Blue']]);
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Greencard', 'colors' => ['Green']]);

    CardsServer::tool(ListCardsTool::class, ['color' => ['Red', 'Blue']])
        ->assertOk()
        ->assertSee(['Redcard', 'Bluecard'])
        ->assertDontSee('Greencard');
});

it('list-cards filters by counter', function () {
    $pack = Pack::factory()->create();
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Counter2000', 'counter' => 2000]);
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Counter1000', 'counter' => 1000]);

    CardsServer::tool(ListCardsTool::class, ['counter' => 2000])
        ->assertOk()
        ->assertSee('Counter2000')
        ->assertDontSee('Counter1000');
});

it('list-cards filters by exact power', function () {
    $pack = Pack::factory()->create();
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Power5000', 'power' => 5000]);
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Power6000', 'power' => 6000]);

    CardsServer::tool(ListCardsTool::class, ['power' => [5000]])
        ->assertOk()
        ->assertSee('Power5000')
        ->assertDontSee('Power6000');
});

it('list-cards excludes values with a not filter', function () {
    $pack = Pack::factory()->create();
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Redcard', 'colors' => ['Red']]);
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Bluecard', 'colors' => ['Blue']]);

    CardsServer::tool(ListCardsTool::class, ['color_not' => 'Red'])
        ->assertOk()
        ->assertSee('Bluecard')
        ->assertDontSee('Redcard');
});

it('list-cards filters by presence of trigger, effect and counter', function (string $filter, string $column, string|int $value) {
    $pack = Pack::factory()->create();
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Withfield', $column => $value]);
    Card::factory()->create(['pack_id' => $pack->id, 'name' => 'Withoutfield', $column => null]);

    CardsServer::tool(ListCardsTool::class, [$filter => true])
        ->assertOk()
        ->assertSee('Withfield')
        ->assertDontSee('Withoutfield');

    CardsServer::tool(ListCardsTool::class, [$filter => false])
        ->assertOk()
        ->assertSee('Withoutfield')
        ->assertDontSee('Withfield');
})->with([
    'trigger' => ['has_trigger', 'trigger', '[Trigger] Draw 1.'],
    'effect' => ['has_effect', 'effect', '[Blocker]'],
    'counter' => ['has_counter', 'counter', 2000],
]);

it('list-cards rejects a limit outside 1 to 100', function (int $limit) {
    Card::factory()->count(3)->create();

    CardsServer::tool(ListCardsTool::class, ['limit' => $limit])
        ->assertHasErrors();
})->with([0, 101]);

it('list-cards rejects invalid filter values', function (array $arguments) {
    Card::factory()->count(3)->create();

    CardsServer::tool(ListCardsTool::class, $arguments)
        ->assertHasErrors();
})->with([
    'unknown category' => [['category' => 'Foo']],
    'unknown category in list' => [['category_not' => ['Leader', 'Foo']]],
    'unknown color' => [['color' => 'Pink']],
    'unknown color_not' => [['color_not' => ['Pink']]],
    'boolean as string' => [['has_trigger' => 'false']],
    'nested list' => [['rarity' => [['Rare']]]],
    'cost as text' => [['cost' => 'cheap']],
    'cost_min as text' => [['cost_min' => 'low']],
]);

it('list-cards names the MCP argument in validation errors', function () {
    CardsServer::tool(ListCardsTool::class, ['limit' => 0])
        ->assertHasErrors(['The limit field']);

    CardsServer::tool(ListCardsTool::class, ['pack_label' => ['a']])
        ->assertHasErrors(['pack_label']);
});
