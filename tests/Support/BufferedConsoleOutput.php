<?php

namespace Tests\Support;

use LogicException;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console output that keeps stdout and stderr in separate buffers, so tests can
 * assert which stream a message went to.
 */
class BufferedConsoleOutput extends BufferedOutput implements ConsoleOutputInterface
{
    private OutputInterface $errorOutput;

    public function __construct()
    {
        parent::__construct(self::VERBOSITY_NORMAL, false);

        $this->errorOutput = new BufferedOutput(self::VERBOSITY_NORMAL, false);
    }

    public function getErrorOutput(): OutputInterface
    {
        return $this->errorOutput;
    }

    public function setErrorOutput(OutputInterface $error): void
    {
        $this->errorOutput = $error;
    }

    public function errorFetch(): string
    {
        return $this->errorOutput instanceof BufferedOutput ? $this->errorOutput->fetch() : '';
    }

    public function section(): never
    {
        throw new LogicException('Console sections are not supported in tests.');
    }
}
