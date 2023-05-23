<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\GarbageCollection;

use function gc_collect_cycles;
use function gc_disable;
use function gc_enable;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GarbageCollectionHandler
{
    private readonly int $threshold;
    private int $tests = 0;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Facade $facade, int $threshold)
    {
        $this->threshold = $threshold;

        $this->registerSubscribers($facade);
    }

    public function executionStarted(): void
    {
        gc_disable();
        gc_collect_cycles();
    }

    public function executionFinished(): void
    {
        gc_collect_cycles();
        gc_enable();
    }

    public function testFinished(): void
    {
        $this->tests++;

        if ($this->tests === $this->threshold) {
            gc_collect_cycles();

            $this->tests = 0;
        }
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new ExecutionStartedSubscriber($this),
            new ExecutionFinishedSubscriber($this),
            new TestFinishedSubscriber($this),
        );
    }
}
