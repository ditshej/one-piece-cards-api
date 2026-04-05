<?php

test('all artisan commands have a corresponding test file', function () {
    $commandFiles = glob(app_path('Console/Commands/*.php')) ?: [];

    if (empty($commandFiles)) {
        $this->markTestSkipped('No artisan commands found to check.');
    }

    foreach ($commandFiles as $commandFile) {
        $commandName = basename($commandFile, '.php');
        $testFile = base_path("tests/Feature/Commands/{$commandName}Test.php");

        expect(file_exists($testFile))
            ->toBeTrue("Missing test file for command: {$commandName}");
    }
});
