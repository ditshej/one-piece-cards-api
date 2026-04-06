<?php

use App\Models\Card;
use App\Models\Pack;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

it('returns 404 for a missing card', function () {
    $this->getJson('/api/v1/cards/INVALID-999')->assertNotFound();
});

it('does not respond on unversioned api path', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create();

    $this->getJson('/api/cards')->assertNotFound();
});

it('returns empty data array when no cards exist', function () {
    $this->getJson('/api/v1/cards')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('filters cards by color', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['colors' => ['Red']]);
    Card::factory()->for($pack)->create(['colors' => ['Blue']]);
    Card::factory()->for($pack)->create(['colors' => ['Red', 'Green']]);

    $response = $this->getJson('/api/v1/cards?color=Red')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by category', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['category' => 'Leader']);
    Card::factory()->for($pack)->create(['category' => 'Character']);
    Card::factory()->for($pack)->create(['category' => 'Leader']);

    $response = $this->getJson('/api/v1/cards?category=Leader')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by cost', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 5]);
    Card::factory()->for($pack)->create(['cost' => 3]);
    Card::factory()->for($pack)->create(['cost' => 5]);

    $response = $this->getJson('/api/v1/cards?cost=5')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by pack label', function () {
    $pack1 = Pack::factory()->create(['label' => 'OP-15']);
    $pack2 = Pack::factory()->create(['label' => 'OP-01']);
    Card::factory()->for($pack1)->count(2)->create();
    Card::factory()->for($pack2)->create();

    $response = $this->getJson('/api/v1/cards?pack=OP-15')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('combines multiple filters', function () {
    $pack = Pack::factory()->create(['label' => 'OP-15']);
    Card::factory()->for($pack)->create(['colors' => ['Red'], 'cost' => 5]);
    Card::factory()->for($pack)->create(['colors' => ['Red'], 'cost' => 3]);
    Card::factory()->for($pack)->create(['colors' => ['Blue'], 'cost' => 5]);

    $response = $this->getJson('/api/v1/cards?color=Red&cost=5')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('searches cards by effect text', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => 'Draw 2 cards', 'trigger' => null]);
    Card::factory()->for($pack)->create(['effect' => 'Give +1000 power', 'trigger' => null]);

    $response = $this->getJson('/api/v1/cards?search=draw')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('searches cards by trigger text', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => null, 'trigger' => 'Draw 1 card']);
    Card::factory()->for($pack)->create(['effect' => null, 'trigger' => null]);

    $response = $this->getJson('/api/v1/cards?search=draw')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('paginates card results', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->count(20)->create();

    $response = $this->getJson('/api/v1/cards')->assertOk();

    expect($response->json('data'))->toHaveCount(15)
        ->and($response->json('meta.total'))->toBe(20)
        ->and($response->json('meta.last_page'))->toBe(2);
});

it('returns second page of results', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->count(20)->create();

    $response = $this->getJson('/api/v1/cards?page=2')->assertOk();

    expect($response->json('data'))->toHaveCount(5)
        ->and($response->json('meta.current_page'))->toBe(2);
});

it('returns a single card on show', function () {
    $pack = Pack::factory()->create(['id' => 'OP01']);
    Card::factory()->for($pack)->create([
        'id' => 'OP01-001',
        'name' => 'Monkey.D.Luffy',
    ]);

    $this->getJson('/api/v1/cards/OP01-001')
        ->assertOk()
        ->assertJsonPath('data.id', 'OP01-001')
        ->assertJsonPath('data.name', 'Monkey.D.Luffy')
        ->assertJsonPath('data.pack_id', 'OP01');
});

it('returns paginated cards on index', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->count(3)->create();

    $this->getJson('/api/v1/cards')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'pack_id', 'card_set', 'name', 'rarity', 'category', 'colors', 'cost', 'power', 'counter', 'attributes', 'types', 'effect', 'trigger', 'img_url', 'alt_art_variant']],
            'links',
            'meta',
        ]);
});

it('filters cards by name (partial, case-insensitive)', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['name' => 'Monkey D. Luffy']);
    Card::factory()->for($pack)->create(['name' => 'Roronoa Zoro']);

    $response = $this->getJson('/api/v1/cards?name=luffy')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Monkey D. Luffy');
});

it('filters cards by rarity', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['rarity' => 'Rare']);
    Card::factory()->for($pack)->create(['rarity' => 'Uncommon']);
    Card::factory()->for($pack)->create(['rarity' => 'Rare']);

    $response = $this->getJson('/api/v1/cards?rarity=Rare')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by attribute', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['attributes' => ['Wisdom']]);
    Card::factory()->for($pack)->create(['attributes' => ['Strike']]);
    Card::factory()->for($pack)->create(['attributes' => ['Wisdom', 'Slash']]);

    $response = $this->getJson('/api/v1/cards?attribute=Wisdom')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by type', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['types' => ['Egghead']]);
    Card::factory()->for($pack)->create(['types' => ['Straw Hat Crew']]);
    Card::factory()->for($pack)->create(['types' => ['Egghead', 'Scientist']]);

    $response = $this->getJson('/api/v1/cards?type=Egghead')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by minimum cost', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 4]);
    Card::factory()->for($pack)->create(['cost' => 6]);
    Card::factory()->for($pack)->create(['cost' => 8]);

    $response = $this->getJson('/api/v1/cards?cost_min=6')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by cost range', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 2]);
    Card::factory()->for($pack)->create(['cost' => 5]);
    Card::factory()->for($pack)->create(['cost' => 9]);

    $response = $this->getJson('/api/v1/cards?cost_min=4&cost_max=6')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.cost'))->toBe(5);
});

it('filters cards by minimum power', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['power' => 2000]);
    Card::factory()->for($pack)->create(['power' => 5000]);
    Card::factory()->for($pack)->create(['power' => 9000]);

    $response = $this->getJson('/api/v1/cards?power_min=5000')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by keyword matching [Keyword] bracket syntax', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => '[Blocker] (After your opponent declares an attack, you may rest this card)', 'trigger' => null]);
    Card::factory()->for($pack)->create(['effect' => '[DON!! x2] [When Attacking] Your opponent cannot activate a [Blocker] Character during this battle.', 'trigger' => null]);
    Card::factory()->for($pack)->create(['effect' => 'Draw 2 cards.', 'trigger' => null]);

    $response = $this->getJson('/api/v1/cards?keyword=Blocker')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by keyword also matching trigger text', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => null, 'trigger' => '[Blocker] Activate this.']);
    Card::factory()->for($pack)->create(['effect' => null, 'trigger' => 'Draw 1 card.']);

    $response = $this->getJson('/api/v1/cards?keyword=Blocker')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('filters alt art cards only', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['id' => 'OP13-113']);
    Card::factory()->for($pack)->create(['id' => 'OP13-113_p1']);
    Card::factory()->for($pack)->create(['id' => 'OP13-114']);

    $response = $this->getJson('/api/v1/cards?alt_art=1')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.id'))->toBe('OP13-113_p1');
});

it('filters cards by card_set', function () {
    $pack1 = Pack::factory()->create();
    $pack2 = Pack::factory()->create();
    Card::factory()->for($pack1)->create(['id' => 'OP03-001']);
    Card::factory()->for($pack1)->create(['id' => 'OP03-002']);
    Card::factory()->for($pack2)->create(['id' => 'OP01-001']);

    $response = $this->getJson('/api/v1/cards?card_set=OP03')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('returns card_set and alt_art_variant in response', function () {
    $pack = Pack::factory()->create(['id' => 'OP13']);
    Card::factory()->for($pack)->create(['id' => 'OP13-113']);
    Card::factory()->for($pack)->create(['id' => 'OP13-113_p2']);

    $response = $this->getJson('/api/v1/cards?card_set=OP13')->assertOk();

    $data = collect($response->json('data'))->keyBy('id');
    expect($data['OP13-113']['card_set'])->toBe('OP13')
        ->and($data['OP13-113']['alt_art_variant'])->toBeNull()
        ->and($data['OP13-113_p2']['card_set'])->toBe('OP13')
        ->and($data['OP13-113_p2']['alt_art_variant'])->toBe(2);
});

it('respects per_page parameter', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->count(10)->create();

    $response = $this->getJson('/api/v1/cards?per_page=3')->assertOk();

    expect($response->json('data'))->toHaveCount(3)
        ->and($response->json('meta.per_page'))->toBe(3);
});

it('combines pack label, color, and rarity filters', function () {
    $pack1 = Pack::factory()->create(['label' => 'OP-01']);
    $pack2 = Pack::factory()->create(['label' => 'OP-02']);
    Card::factory()->for($pack1)->create(['colors' => ['Red'], 'rarity' => 'Uncommon']);
    Card::factory()->for($pack1)->create(['colors' => ['Red'], 'rarity' => 'Rare']);
    Card::factory()->for($pack1)->create(['colors' => ['Blue'], 'rarity' => 'Uncommon']);
    Card::factory()->for($pack2)->create(['colors' => ['Red'], 'rarity' => 'Uncommon']);

    $response = $this->getJson('/api/v1/cards?pack=OP-01&color=Red&rarity=Uncommon')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('filters cards by cost=0 correctly (zero is a valid cost)', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 0]);
    Card::factory()->for($pack)->create(['cost' => 3]);

    $response = $this->getJson('/api/v1/cards?cost=0')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.cost'))->toBe(0);
});

it('returns 422 for non-numeric cost_min', function () {
    $this->getJson('/api/v1/cards?cost_min=abc')->assertUnprocessable();
});

it('filters cards by multiple cost values using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 3]);
    Card::factory()->for($pack)->create(['cost' => 5]);
    Card::factory()->for($pack)->create(['cost' => 7]);

    $response = $this->getJson('/api/v1/cards?cost[]=3&cost[]=5')->assertOk();

    expect($response->json('data'))->toHaveCount(2)
        ->and(collect($response->json('data'))->pluck('cost')->sort()->values()->all())->toBe([3, 5]);
});

it('filters cards by single-element cost array equivalently to scalar cost', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 5]);
    Card::factory()->for($pack)->create(['cost' => 3]);

    $response = $this->getJson('/api/v1/cards?cost[]=5')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.cost'))->toBe(5);
});

it('returns 422 for non-integer cost array item', function () {
    $this->getJson('/api/v1/cards?cost[]=abc')->assertUnprocessable();
});

it('returns 422 for per_page above maximum', function () {
    $this->getJson('/api/v1/cards?per_page=999')->assertUnprocessable();
});

it('filters cards by multiple colors using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['colors' => ['Red']]);
    Card::factory()->for($pack)->create(['colors' => ['Blue']]);
    Card::factory()->for($pack)->create(['colors' => ['Yellow']]);
    Card::factory()->for($pack)->create(['colors' => ['Green']]);

    $response = $this->getJson('/api/v1/cards?color[]=Red&color[]=Yellow')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by multiple rarities using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['rarity' => 'SR']);
    Card::factory()->for($pack)->create(['rarity' => 'SEC']);
    Card::factory()->for($pack)->create(['rarity' => 'R']);

    $response = $this->getJson('/api/v1/cards?rarity[]=SR&rarity[]=SEC')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by multiple card sets using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['id' => 'OP13-001']);
    Card::factory()->for($pack)->create(['id' => 'OP15-001']);
    Card::factory()->for($pack)->create(['id' => 'OP01-099']);

    $response = $this->getJson('/api/v1/cards?card_set[]=OP13&card_set[]=OP15')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by multiple categories using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['category' => 'Character']);
    Card::factory()->for($pack)->create(['category' => 'Leader']);
    Card::factory()->for($pack)->create(['category' => 'Event']);

    $response = $this->getJson('/api/v1/cards?category[]=Character&category[]=Leader')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('returns 422 for invalid category array value', function () {
    $this->getJson('/api/v1/cards?category[]=Invalid')->assertUnprocessable();
});

it('filters cards by multiple types using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['types' => ['Minks']]);
    Card::factory()->for($pack)->create(['types' => ['Strawhats']]);
    Card::factory()->for($pack)->create(['types' => ['Navy']]);

    $response = $this->getJson('/api/v1/cards?type[]=Minks&type[]=Strawhats')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by multiple attributes using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['attributes' => ['Wisdom']]);
    Card::factory()->for($pack)->create(['attributes' => ['Strike']]);
    Card::factory()->for($pack)->create(['attributes' => ['Slash']]);

    $response = $this->getJson('/api/v1/cards?attribute[]=Wisdom&attribute[]=Strike')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by multiple keywords using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => '[Blocker] Do something', 'trigger' => null]);
    Card::factory()->for($pack)->create(['effect' => '[Rush] Attack immediately', 'trigger' => null]);
    Card::factory()->for($pack)->create(['effect' => 'Draw 2 cards', 'trigger' => null]);

    $response = $this->getJson('/api/v1/cards?keyword[]=Blocker&keyword[]=Rush')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards by multiple power values using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['power' => 8000]);
    Card::factory()->for($pack)->create(['power' => 10000]);
    Card::factory()->for($pack)->create(['power' => 5000]);

    $response = $this->getJson('/api/v1/cards?power[]=8000&power[]=10000')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('returns 422 for non-integer power array item', function () {
    $this->getJson('/api/v1/cards?power[]=abc')->assertUnprocessable();
});

it('excludes cards by color_not', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['colors' => ['Red']]);
    Card::factory()->for($pack)->create(['colors' => ['Blue']]);
    Card::factory()->for($pack)->create(['colors' => ['Red', 'Green']]);

    $response = $this->getJson('/api/v1/cards?color_not[]=Red')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.colors'))->toBe(['Blue']);
});

it('excludes cards by multiple rarity_not values', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['rarity' => 'C']);
    Card::factory()->for($pack)->create(['rarity' => 'UC']);
    Card::factory()->for($pack)->create(['rarity' => 'R']);

    $response = $this->getJson('/api/v1/cards?rarity_not[]=C&rarity_not[]=UC')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.rarity'))->toBe('R');
});

it('excludes cards by card_set_not', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['card_set' => 'OP01']);
    Card::factory()->for($pack)->create(['card_set' => 'OP01']);
    Card::factory()->for($pack)->create(['card_set' => 'OP02']);

    $response = $this->getJson('/api/v1/cards?card_set_not[]=OP01')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.card_set'))->toBe('OP02');
});

it('excludes cards by category_not', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['category' => 'Leader']);
    Card::factory()->for($pack)->create(['category' => 'Character']);
    Card::factory()->for($pack)->create(['category' => 'Event']);

    $response = $this->getJson('/api/v1/cards?category_not[]=Leader')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('returns 422 for invalid category_not value', function () {
    $this->getJson('/api/v1/cards?category_not[]=Invalid')->assertUnprocessable();
});

it('excludes cards by type_not', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['types' => ['Navy']]);
    Card::factory()->for($pack)->create(['types' => ['Straw Hat Crew']]);
    Card::factory()->for($pack)->create(['types' => ['Navy', 'Pirate']]);

    $response = $this->getJson('/api/v1/cards?type_not[]=Navy')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.types'))->toBe(['Straw Hat Crew']);
});

it('excludes cards by attribute_not', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['attributes' => ['Slash']]);
    Card::factory()->for($pack)->create(['attributes' => ['Strike']]);
    Card::factory()->for($pack)->create(['attributes' => ['Wisdom']]);

    $response = $this->getJson('/api/v1/cards?attribute_not[]=Slash')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('excludes cards with either attribute when using multiple attribute_not values', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['attributes' => ['Slash']]);
    Card::factory()->for($pack)->create(['attributes' => ['Strike']]);
    Card::factory()->for($pack)->create(['attributes' => ['Wisdom']]);
    Card::factory()->for($pack)->create(['attributes' => ['Slash', 'Strike']]);

    $response = $this->getJson('/api/v1/cards?attribute_not[]=Slash&attribute_not[]=Strike')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.attributes'))->toBe(['Wisdom']);
});

it('excludes cards with keyword_not matching effect or trigger', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => '[Blocker] Guard this.', 'trigger' => 'Do nothing.']);
    Card::factory()->for($pack)->create(['effect' => 'Attack now.', 'trigger' => '[Blocker] Activate.']);
    Card::factory()->for($pack)->create(['effect' => 'Draw 2 cards.', 'trigger' => 'Draw 1 card.']);

    $response = $this->getJson('/api/v1/cards?keyword_not[]=Blocker')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('excludes cards by multiple cost_not values', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['cost' => 9]);
    Card::factory()->for($pack)->create(['cost' => 10]);
    Card::factory()->for($pack)->create(['cost' => 5]);

    $response = $this->getJson('/api/v1/cards?cost_not[]=9&cost_not[]=10')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.cost'))->toBe(5);
});

it('returns 422 for non-integer cost_not value', function () {
    $this->getJson('/api/v1/cards?cost_not[]=abc')->assertUnprocessable();
});

it('excludes cards by power_not', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['power' => 9000]);
    Card::factory()->for($pack)->create(['power' => 5000]);
    Card::factory()->for($pack)->create(['power' => 3000]);

    $response = $this->getJson('/api/v1/cards?power_not[]=9000')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('returns 422 for non-integer power_not value', function () {
    $this->getJson('/api/v1/cards?power_not[]=abc')->assertUnprocessable();
});

it('filters cards by counter value', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['counter' => 1000]);
    Card::factory()->for($pack)->create(['counter' => 2000]);
    Card::factory()->for($pack)->create(['counter' => null]);

    $response = $this->getJson('/api/v1/cards?counter[]=1000')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.counter'))->toBe(1000);
});

it('filters cards by multiple counter values using array notation', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['counter' => 1000]);
    Card::factory()->for($pack)->create(['counter' => 2000]);
    Card::factory()->for($pack)->create(['counter' => null]);

    $response = $this->getJson('/api/v1/cards?counter[]=1000&counter[]=2000')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('returns 422 for non-integer counter value', function () {
    $this->getJson('/api/v1/cards?counter[]=abc')->assertUnprocessable();
});

it('excludes cards by counter_not including cards with null counter', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['counter' => 1000]);
    Card::factory()->for($pack)->create(['counter' => 2000]);
    Card::factory()->for($pack)->create(['counter' => null]);

    $response = $this->getJson('/api/v1/cards?counter_not[]=2000')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards with a trigger using has_trigger=true', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['trigger' => 'Draw 1 card.']);
    Card::factory()->for($pack)->create(['trigger' => null]);
    Card::factory()->for($pack)->create(['trigger' => 'Add 1 DON!!.']);

    $response = $this->getJson('/api/v1/cards?has_trigger=true')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards without a trigger using has_trigger=false', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['trigger' => 'Draw 1 card.']);
    Card::factory()->for($pack)->create(['trigger' => null]);
    Card::factory()->for($pack)->create(['trigger' => null]);

    $response = $this->getJson('/api/v1/cards?has_trigger=false')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards with an effect using has_effect=true', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => '[Blocker] Guard.']);
    Card::factory()->for($pack)->create(['effect' => null]);
    Card::factory()->for($pack)->create(['effect' => 'Draw 2 cards.']);

    $response = $this->getJson('/api/v1/cards?has_effect=true')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards without an effect using has_effect=false', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['effect' => '[Blocker] Guard.']);
    Card::factory()->for($pack)->create(['effect' => null]);
    Card::factory()->for($pack)->create(['effect' => null]);

    $response = $this->getJson('/api/v1/cards?has_effect=false')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards with a counter using has_counter=true', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['counter' => 1000]);
    Card::factory()->for($pack)->create(['counter' => null]);
    Card::factory()->for($pack)->create(['counter' => 2000]);

    $response = $this->getJson('/api/v1/cards?has_counter=true')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('filters cards without a counter using has_counter=false', function () {
    $pack = Pack::factory()->create();
    Card::factory()->for($pack)->create(['counter' => 1000]);
    Card::factory()->for($pack)->create(['counter' => null]);
    Card::factory()->for($pack)->create(['counter' => null]);

    $response = $this->getJson('/api/v1/cards?has_counter=false')->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});
