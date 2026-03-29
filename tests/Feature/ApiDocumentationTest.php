<?php

it('serves the openapi json spec', function () {
    $this->get('/docs/api.json')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/json');
});

it('serves the stoplight elements ui', function () {
    $this->get('/docs/api')
        ->assertOk();
});
