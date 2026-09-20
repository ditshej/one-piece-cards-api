<?php

namespace Tests\Support;

use App\Console\Commands\ResolveCardsCommand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

/**
 * Stand-in for `cards:resolve` that reports standard input as an interactive
 * terminal, which cannot be simulated with an in-memory stream.
 */
#[Signature('cards:resolve-interactive {file?} {--format=json} {--deck}')]
#[Description('Test double that treats standard input as a terminal')]
class InteractiveResolveCardsCommand extends ResolveCardsCommand
{
    /** @param resource $stream */
    protected function stdinIsInteractive($stream): bool
    {
        return true;
    }
}
