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
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GarbageCollectionHandler
{
    private readonly Emitter $emitter;
    private readonly int $threshold;
    private int $tests = 0;

    /**
     * The tests, by their ID, that were counted when their Test\Finished
     * event was collected rather than dispatched: the attempts of a retried
     * test or the examples of a property, for instance. The Test\Finished
     * event that is dispatched for such a test afterwards, the forwarded one
     * of the last attempt or the one that is reported for the property, is
     * not counted again.
     *
     * @var array<non-empty-string, true>
     */
    private array $countedWhileEventsWereCollected = [];

    public function __construct(Facade $facade, int $threshold)
    {
        $this->emitter   = $facade->emitter();
        $this->threshold = $threshold;

        $this->registerSubscribers($facade);
    }

    public function executionStarted(): void
    {
        gc_disable();

        $this->emitter->testRunnerDisabledGarbageCollection();

        gc_collect_cycles();

        $this->emitter->testRunnerTriggeredGarbageCollection();
    }

    public function executionFinished(): void
    {
        gc_collect_cycles();

        $this->emitter->testRunnerTriggeredGarbageCollection();

        gc_enable();

        $this->emitter->testRunnerEnabledGarbageCollection();
    }

    public function testFinished(Finished $event): void
    {
        $id = $event->test()->id();

        if (isset($this->countedWhileEventsWereCollected[$id])) {
            unset($this->countedWhileEventsWereCollected[$id]);

            return;
        }

        $this->count();
    }

    /**
     * Garbage that the tests whose events are collected rather than
     * dispatched leave behind is collected as well. The event that reports
     * this is collected with the events of the test.
     */
    public function testFinishedWhileEventsAreCollected(Finished $event): void
    {
        $this->countedWhileEventsWereCollected[$event->test()->id()] = true;

        $this->count();
    }

    private function count(): void
    {
        $this->tests++;

        if ($this->tests === $this->threshold) {
            gc_collect_cycles();

            $this->emitter->testRunnerTriggeredGarbageCollection();

            $this->tests = 0;
        }
    }

    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new ExecutionStartedSubscriber($this),
            new ExecutionFinishedSubscriber($this),
            new TestFinishedSubscriber($this),
        );

        $facade->registerSubscribersForCollectedEvents(
            new TestFinishedWhileEventsAreCollectedSubscriber($this),
        );
    }
}
