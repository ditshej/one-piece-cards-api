<?php

use Illuminate\Console\Scheduling\Schedule;

beforeEach(function () {
    $schedule = app(Schedule::class);

    $this->importEvent = collect($schedule->events())
        ->first(fn ($event) => str_contains($event->command ?? '', 'cards:import'));
});

it('schedules cards:import to run weekly', function () {
    expect($this->importEvent)->not->toBeNull()
        ->and($this->importEvent->expression)->toBe('0 0 * * 0');
});

it('runs scheduled import when enabled', function () {
    config(['import.schedule_enabled' => true]);

    expect($this->importEvent->filtersPass(app()))->toBeTrue();
});

it('skips scheduled import when disabled', function () {
    config(['import.schedule_enabled' => false]);

    expect($this->importEvent->filtersPass(app()))->toBeFalse();
});
