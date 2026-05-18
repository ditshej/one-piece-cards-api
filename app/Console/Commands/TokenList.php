<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

#[Signature('token:list {--json : Output as JSON}')]
#[Description('List all Sanctum API tokens with metadata')]
class TokenList extends Command
{
    public function handle(): int
    {
        $tokens = PersonalAccessToken::with('tokenable')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'tokenable_type', 'tokenable_id', 'last_used_at', 'created_at']);

        if ($tokens->isEmpty()) {
            $this->components->info('No tokens found.');

            return self::SUCCESS;
        }

        $data = $tokens->map(fn (PersonalAccessToken $token) => [
            'id' => $token->id,
            'name' => $token->name,
            'email' => $token->tokenable?->email,
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'created_at' => $token->created_at->toIso8601String(),
        ]);

        if ($this->option('json')) {
            $this->line($data->toJson(JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $rows = $tokens->map(fn (PersonalAccessToken $token) => [
            $token->id,
            $token->name,
            $token->tokenable?->email ?? 'N/A',
            $token->last_used_at?->diffForHumans() ?? 'Never',
            $token->created_at->toDateTimeString(),
        ]);

        $this->table(['ID', 'Name', 'User', 'Last used', 'Created'], $rows);

        return self::SUCCESS;
    }
}
