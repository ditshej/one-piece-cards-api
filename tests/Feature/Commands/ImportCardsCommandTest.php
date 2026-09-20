<?php

use App\Models\Card;
use App\Models\Pack;
use App\Models\User;

beforeEach(function () {
    $this->fixturePath = __DIR__.'/../../Fixtures/vegapull';
});

it('never writes to auth tables', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');
    $originalAttributes = $user->only(['name', 'email', 'password']);

    $this->artisan('cards:import', ['path' => $this->fixturePath])
        ->assertSuccessful();

    expect(User::count())->toBe(1)
        ->and($user->refresh()->only(['name', 'email', 'password']))->toBe($originalAttributes)
        ->and($user->tokens()->count())->toBe(1)
        ->and($user->tokens()->first()->id)->toBe($token->accessToken->id);
});

it('imports packs and cards from vegapull JSON files', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath])
        ->assertSuccessful();

    expect(Pack::count())->toBe(1)
        ->and(Pack::first()->id)->toBe('569101')
        ->and(Pack::first()->name)->toBe('ROMANCE DAWN')
        ->and(Pack::first()->label)->toBe('OP-01')
        ->and(Card::count())->toBe(3);
});

it('is idempotent', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath]);
    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Pack::count())->toBe(1)
        ->and(Card::count())->toBe(3);
});

it('updates existing card data', function () {
    Card::factory()->create([
        'id' => 'OP01-001',
        'pack_id' => Pack::factory()->create(['id' => '569101'])->id,
        'power' => 3000,
    ]);

    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Card::find('OP01-001')->power)->toBe(5000);
});

it('uses default config path when no argument given', function () {
    config(['import.vegapull_path' => $this->fixturePath]);

    $this->artisan('cards:import')
        ->assertSuccessful();

    expect(Card::count())->toBe(3);
});

it('warns when no JSON files are found', function () {
    $emptyDir = sys_get_temp_dir().'/empty-vegapull-'.uniqid();
    mkdir($emptyDir);
    mkdir($emptyDir.'/json');

    try {
        $this->artisan('cards:import', ['path' => $emptyDir])
            ->expectsOutputToContain('No card JSON files found');

        expect(Card::count())->toBe(0);
    } finally {
        rmdir($emptyDir.'/json');
        rmdir($emptyDir);
    }
});

it('skips empty or invalid card JSON files', function () {
    $tempDir = sys_get_temp_dir().'/bad-vegapull-'.uniqid();
    mkdir($tempDir.'/json', 0777, true);
    file_put_contents($tempDir.'/json/cards_bad.json', 'not valid json');

    try {
        $this->artisan('cards:import', ['path' => $tempDir])
            ->expectsOutputToContain('Skipping empty or invalid file');

        expect(Card::count())->toBe(0);
    } finally {
        unlink($tempDir.'/json/cards_bad.json');
        rmdir($tempDir.'/json');
        rmdir($tempDir);
    }
});

it('displays import summary', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath])
        ->expectsOutputToContain('Imported 3 cards');
});

it('uses img_full_url for card image', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Card::find('OP01-001')->img_url)
        ->toBe('https://en.onepiece-cardgame.com/images/cardlist/card/OP01-001.png?260325');
});

it('strips the scraped Type label from card types', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Card::find('OP01-001')->types)->toBe(['Supernovas', 'Straw Hat Crew']);
});

it('strips the scraped Trigger label from the trigger text', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Card::find('OP01-006')->trigger)->toBe('[Trigger] Play this card.');
});

it('decodes HTML entities in card name and effect', function () {
    $this->artisan('cards:import', ['path' => $this->fixturePath]);

    expect(Card::find('OP01-004'))
        ->name->toBe('Shachi & Penguin')
        ->effect->toBe('[On Play] Rest Shachi & Penguin.');
});

it('decodes HTML entities in pack names', function () {
    $tempDir = sys_get_temp_dir().'/entity-vegapull-'.uniqid();
    mkdir($tempDir.'/json', 0777, true);
    file_put_contents($tempDir.'/json/packs.json', json_encode([
        '569022' => [
            'id' => '569022',
            'raw_title' => 'STARTER DECK -Ace &amp; Newgate- [ST-22]',
            'title_parts' => ['prefix' => 'STARTER DECK', 'title' => 'Ace &amp; Newgate', 'label' => 'ST-22'],
        ],
    ]));
    file_put_contents($tempDir.'/json/cards_569022.json', json_encode([
        [
            'id' => 'ST22-001',
            'pack_id' => '569022',
            'name' => 'Portgas.D.Ace',
            'rarity' => 'Leader',
            'category' => 'Leader',
            'img_url' => '../images/cardlist/card/ST22-001.png',
            'cost' => null,
            'attributes' => ['Special'],
            'power' => 5000,
            'counter' => null,
            'colors' => ['Red'],
            'types' => ['Whitebeard Pirates'],
            'effect' => null,
            'trigger' => null,
        ],
    ]));

    try {
        $this->artisan('cards:import', ['path' => $tempDir])
            ->assertSuccessful();

        expect(Pack::find('569022')->name)->toBe('Ace & Newgate');
    } finally {
        unlink($tempDir.'/json/packs.json');
        unlink($tempDir.'/json/cards_569022.json');
        rmdir($tempDir.'/json');
        rmdir($tempDir);
    }
});

it('leaves already clean values untouched', function () {
    $tempDir = sys_get_temp_dir().'/clean-vegapull-'.uniqid();
    mkdir($tempDir.'/json', 0777, true);
    file_put_contents($tempDir.'/json/packs.json', json_encode([
        '569101' => [
            'id' => '569101',
            'raw_title' => 'BOOSTER PACK -ROMANCE DAWN- [OP-01]',
            'title_parts' => ['prefix' => 'BOOSTER PACK', 'title' => 'ROMANCE DAWN', 'label' => 'OP-01'],
        ],
    ]));
    file_put_contents($tempDir.'/json/cards_569101.json', json_encode([
        [
            'id' => 'OP01-001',
            'pack_id' => '569101',
            'name' => 'Roronoa Zoro',
            'rarity' => 'Leader',
            'category' => 'Leader',
            'img_url' => '../images/cardlist/card/OP01-001.png',
            'cost' => null,
            'attributes' => ['Slash'],
            'power' => 5000,
            'counter' => null,
            'colors' => ['Red'],
            'types' => ['Typhoon', 'Straw Hat Crew'],
            'effect' => '[DON!! x1] [Your Turn] All of your Characters gain +1000 power.',
            'trigger' => '[Trigger] Play this card.',
        ],
    ]));

    try {
        $this->artisan('cards:import', ['path' => $tempDir])
            ->assertSuccessful();

        $card = Card::find('OP01-001');

        expect($card->types)->toBe(['Typhoon', 'Straw Hat Crew'])
            ->and($card->name)->toBe('Roronoa Zoro')
            ->and($card->trigger)->toBe('[Trigger] Play this card.')
            ->and($card->effect)->toBe('[DON!! x1] [Your Turn] All of your Characters gain +1000 power.')
            ->and(Pack::find('569101')->name)->toBe('ROMANCE DAWN');
    } finally {
        unlink($tempDir.'/json/packs.json');
        unlink($tempDir.'/json/cards_569101.json');
        rmdir($tempDir.'/json');
        rmdir($tempDir);
    }
});
