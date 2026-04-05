<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

#[Signature('token:revoke {name}')]
#[Description('Revoke all Sanctum API tokens for a given app name')]
class TokenRevoke extends Command
{
    public function handle(): int
    {
        $name = $this->argument('name');

        $deleted = PersonalAccessToken::where('name', $name)->delete();

        if ($deleted === 0) {
            $this->components->warn("No tokens found for [{$name}].");

            return self::FAILURE;
        }

        $this->components->info("Revoked {$deleted} token(s) for [{$name}].");

        return self::SUCCESS;
    }
}
