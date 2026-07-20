<?php

$setSyncPhpEnv = function (?string $value): void {
    if ($value === null) {
        putenv('SYNC_PHP');
        unset($_ENV['SYNC_PHP'], $_SERVER['SYNC_PHP']);

        return;
    }

    putenv("SYNC_PHP={$value}");
    $_ENV['SYNC_PHP'] = $value;
    $_SERVER['SYNC_PHP'] = $value;
};

afterEach(function () use ($setSyncPhpEnv) {
    $setSyncPhpEnv('/opt/php83/bin/php');
});

it('defaults sync_php to php when SYNC_PHP is unset', function () use ($setSyncPhpEnv) {
    $setSyncPhpEnv(null);

    expect((require config_path('import.php'))['sync_php'])->toBe('php');
});

it('defaults sync_php to php when SYNC_PHP is set but blank', function () use ($setSyncPhpEnv) {
    $setSyncPhpEnv('');

    expect((require config_path('import.php'))['sync_php'])->toBe('php');
});

it('uses SYNC_PHP when it is set to a real path', function () use ($setSyncPhpEnv) {
    $setSyncPhpEnv('/opt/php83/bin/php');

    expect((require config_path('import.php'))['sync_php'])->toBe('/opt/php83/bin/php');
});
