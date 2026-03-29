<?php

it('serves the openapi json spec', function () {
    $this->getJson('/docs/api.json')
        ->assertOk();
});

it('serves the stoplight elements ui', function () {
    $this->get('/docs/api')
        ->assertOk();
});
