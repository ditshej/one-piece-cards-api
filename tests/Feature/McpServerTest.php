<?php

use App\Mcp\Servers\CardsServer;
use App\Mcp\Tools\GetCardTool;
use App\Mcp\Tools\GetPackTool;
use App\Mcp\Tools\ListCardsTool;
use App\Mcp\Tools\ListPacksTool;
use App\Models\Card;
use App\Models\Pack;

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
