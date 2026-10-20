<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Runner\Parallel;

use React\Socket\ServerInterface;

/**
 * Represents collection of active processes that are being run in parallel.
 * Inspired by {@see https://github.com/phpstan/phpstan-src/blob/ed68345a82992775112acc2c2bd639d1bd3a1a02/src/Parallel/ProcessPool.php}.
 *
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProcessPool
{
    /**
     * @readonly
     */
    private ServerInterface $server;

    /**
     * @var null|(callable(): void)
     *
     * @readonly
     */
    private $onServerClose;

    /**
     * @var array<string, Process>
     */
    private array $processes = [];

    /**
     * @param null|(callable(): void) $onServerClose
     */
    public function __construct(ServerInterface $server, ?callable $onServerClose = null)
    {
        $this->server = $server;
        $this->onServerClose = $onServerClose;
    }

    public function getProcess(ProcessIdentifier $identifier): Process
    {
        if (!isset($this->processes[$identifier->toString()])) {
            throw ParallelisationException::forUnknownIdentifier($identifier);
        }

        return $this->processes[$identifier->toString()];
    }

    public function addProcess(ProcessIdentifier $identifier, Process $process): void
    {
        $this->processes[$identifier->toString()] = $process;
    }

    public function endProcessIfKnown(ProcessIdentifier $identifier): void
    {
        if (!isset($this->processes[$identifier->toString()])) {
            return;
        }

        $this->endProcess($identifier);
    }

    public function endAll(): void
    {
        foreach ($this->processes as $identifier => $process) {
            $this->endProcessIfKnown(ProcessIdentifier::fromRaw($identifier));
        }
    }

    private function endProcess(ProcessIdentifier $identifier): void
    {
        $this->getProcess($identifier)->quit();

        unset($this->processes[$identifier->toString()]);

        if (0 === \count($this->processes)) {
            $this->server->close();

            if (null !== $this->onServerClose) {
                ($this->onServerClose)();
            }
        }
    }
}
