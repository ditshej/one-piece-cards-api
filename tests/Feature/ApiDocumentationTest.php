<?php

it('serves the openapi json spec', function () {
    $this->getJson('/docs/api.json')
        ->assertOk();
});

it('serves the stoplight elements ui', function () {
    $this->get('/docs/api')
        ->assertOk();
});

it('includes a BearerToken security scheme in the openapi spec', function () {
    $json = $this->getJson('/docs/api.json')->assertOk()->json();

    expect($json['components']['securitySchemes']['BearerToken'])->toMatchArray([
        'type' => 'http',
        'scheme' => 'bearer',
        'bearerFormat' => 'API Key',
    ]);
});

it('applies BearerToken security globally in the openapi spec', function () {
    $json = $this->getJson('/docs/api.json')->assertOk()->json();

    expect($json['security'])->toContain(['BearerToken' => []]);
});
