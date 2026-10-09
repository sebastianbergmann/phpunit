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

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GarbageCollectionHandler
{
    private static ?self $instance = null;
    private readonly Emitter $emitter;
    private readonly int $threshold;
    private int $tests = 0;

    public static function init(Facade $facade, int $threshold): void
    {
        self::$instance = new self($facade, $threshold);
    }

    /**
     * Counts an example of a property toward the number of tests after which
     * garbage is collected.
     *
     * This is needed because the events of the examples of a property are
     * collected rather than dispatched, and a test is otherwise only counted
     * when its Test\Finished event is dispatched.
     */
    public static function propertyExampleFinished(): void
    {
        self::$instance?->testFinished();
    }

    private function __construct(Facade $facade, int $threshold)
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

    public function testFinished(): void
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
    }
}
