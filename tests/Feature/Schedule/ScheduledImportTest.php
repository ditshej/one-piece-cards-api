<?php

use Illuminate\Console\Scheduling\Schedule;

it('schedules cards:import to run weekly', function () {
    $schedule = app(Schedule::class);

    $importEvents = collect($schedule->events())
        ->filter(fn ($event) => str_contains($event->command ?? '', 'cards:import'));

    expect($importEvents)->toHaveCount(1);

    $event = $importEvents->first();
    expect($event->expression)->toBe('0 0 * * 0');
});

it('runs scheduled import when enabled', function () {
    config(['import.schedule_enabled' => true]);

    $schedule = app(Schedule::class);

    $event = collect($schedule->events())
        ->first(fn ($event) => str_contains($event->command ?? '', 'cards:import'));

    expect($event->filtersPass(app()))->toBeTrue();
});

it('skips scheduled import when disabled', function () {
    config(['import.schedule_enabled' => false]);

    $schedule = app(Schedule::class);

    $event = collect($schedule->events())
        ->first(fn ($event) => str_contains($event->command ?? '', 'cards:import'));

    expect($event->filtersPass(app()))->toBeFalse();
});
