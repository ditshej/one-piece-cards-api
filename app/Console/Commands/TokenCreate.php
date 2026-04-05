<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('token:create {name} {email}')]
#[Description('Create a password-less user and issue a Sanctum API token')]
class TokenCreate extends Command
{
    public function handle(): int
    {
        $user = User::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => Str::random(64),
        ]);

        $token = $user->createToken($this->argument('name'));

        $this->components->info("Token created for [{$user->name}].");
        $this->components->warn('Store this token securely — it will not be shown again.');
        $this->line('');
        $this->line("  <fg=green>{$token->plainTextToken}</>");
        $this->line('');

        return self::SUCCESS;
    }
}
