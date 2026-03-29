<?php

it('rejects api requests without authorization header', function () {
    $this->getJson('/api/v1/packs')->assertUnauthorized();
});

it('rejects api requests with wrong api key', function () {
    $this->withHeaders(['Authorization' => 'Bearer wrong-key'])
        ->getJson('/api/v1/packs')
        ->assertUnauthorized();
});

it('accepts api requests with correct api key', function () {
    $this->withHeaders(withApiKey())->getJson('/api/v1/packs')->assertOk();
});

it('rejects mcp requests without authorization header', function () {
    $this->postJson('/mcp')->assertUnauthorized();
});

it('docs remain publicly accessible without authorization', function () {
    $this->get('/docs/api')->assertOk();
    $this->getJson('/docs/api.json')->assertOk();
});
