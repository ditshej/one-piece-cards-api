<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rejects api requests without authorization header', function () {
    $this->getJson('/api/v1/packs')->assertUnauthorized();
});

it('rejects api requests with invalid token', function () {
    $this->withHeaders(['Authorization' => 'Bearer invalid-token'])
        ->getJson('/api/v1/packs')
        ->assertUnauthorized();
});

it('accepts api requests with valid sanctum token', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/packs')->assertOk();
});

it('rejects mcp requests without authorization header', function () {
    $this->postJson('/mcp')->assertUnauthorized();
});

it('accepts mcp requests with valid sanctum token', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/mcp');
    expect($response->status())->not->toBe(401);
});

it('docs remain publicly accessible without authorization', function () {
    $this->get('/docs/api')->assertOk();
    $this->getJson('/docs/api.json')->assertOk();
});

it('updates last_used_at on valid authenticated request', function () {
    $user = User::factory()->create();
    $newToken = $user->createToken('test-app');

    $this->withHeaders(['Authorization' => 'Bearer '.$newToken->plainTextToken])
        ->getJson('/api/v1/packs')
        ->assertOk();

    expect($newToken->accessToken->fresh()->last_used_at)->not->toBeNull();
});
