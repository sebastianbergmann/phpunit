<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function assert;
use function str_starts_with;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event as AnyEvent;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;

/**
 * Aggregates the events of the examples of a property, which are collected
 * rather than dispatched, into the events that are reported for the property.
 *
 * Exactly one sequence of events from Test\PreparationStarted to
 * Test\Finished is reported for a property: the one of a single example, the
 * skeleton, whose Test\PreparationStarted and Test\Prepared events are
 * replaced with those of the first example, so that the duration of the
 * property covers all of its examples. The issues and the Test\ConsideredRisky
 * events of all examples that were checked are reported within that sequence,
 * those of the skeleton included. A Test\ConsideredRisky event and an issue of
 * PHPUnit's own are reported once per message, and the number of assertions is
 * the one that is passed in.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class EventAggregator
{
    /**
     * The messages of Test\ConsideredRisky events that are emitted for each
     * example but are decided for the property as a whole.
     */
    private const array MESSAGES_DECIDED_FOR_THE_PROPERTY = [
        'This test did not perform any assertions',
        'This test is not expected to perform assertions but performed ',
        'This test was aborted after ',
    ];
    private readonly Event\Emitter $emitter;
    private readonly Event\Facade $facade;
    private readonly Code\Test $test;
    private ?PreparationStarted $preparationStarted = null;
    private ?Prepared $prepared                     = null;
    private EventCollection $issues;

    /**
     * @var array<non-empty-string, true>
     */
    private array $reportedMessages = [];
    private bool $issuesDispatched  = false;
    private bool $outcomeDispatched = false;

    public function __construct(Event\Emitter $emitter, Event\Facade $facade, Code\Test $test)
    {
        $this->emitter = $emitter;
        $this->facade  = $facade;
        $this->test    = $test;
        $this->issues  = new EventCollection;
    }

    /**
     * Records the events of an example. Only the Test\PreparationStarted and
     * Test\Prepared events of an example that was not checked, because it was
     * rejected, are kept.
     */
    public function record(EventCollection $events, bool $checked): void
    {
        foreach ($events as $event) {
            if ($event instanceof PreparationStarted && $this->preparationStarted === null) {
                $this->preparationStarted = $event;
            }

            if ($event instanceof Prepared && $this->prepared === null) {
                $this->prepared = $event;
            }

            if ($checked && $this->isReportedOncePerProperty($event)) {
                $this->issues->add($event);
            }
        }
    }

    /**
     * Dispatches the events that are reported for the property.
     *
     * @param ?EventCollection $skeleton           the events of the example whose sequence of events is reported, null when no example ran to its end
     * @param ?Code\Throwable  $error              when not null, replaces the outcome of the skeleton with a Test\Errored event
     * @param bool             $passed             whether a Test\Passed event is dispatched when the skeleton has no outcome
     * @param ?Closure(): void $beforeOutcome      dispatches events that precede the outcome
     * @param Closure(): void  $beforeFinished     dispatches events that precede the Test\Finished event
     * @param non-negative-int $numberOfAssertions
     */
    public function dispatch(?EventCollection $skeleton, ?Code\Throwable $error, bool $passed, ?Closure $beforeOutcome, Closure $beforeFinished, int $numberOfAssertions): void
    {
        if ($skeleton === null) {
            $this->dispatchPreparation();
            $this->dispatchIssues();
            $this->dispatchOutcome(null, $error, $passed, $beforeOutcome);

            $beforeFinished();

            $this->emitter->testFinished($this->test, $numberOfAssertions);

            return;
        }

        $finished = false;

        foreach ($skeleton as $event) {
            if ($event instanceof PreparationStarted) {
                $this->forward($this->preparationStarted ?? $event);

                continue;
            }

            if ($event instanceof Prepared) {
                $this->forward($this->prepared ?? $event);
                $this->dispatchIssues();

                continue;
            }

            if ($this->isIssue($event)) {
                continue;
            }

            if ($this->isOutcome($event)) {
                $this->dispatchIssues();
                $this->dispatchOutcome($event, $error, $passed, $beforeOutcome);

                continue;
            }

            if ($event instanceof Finished) {
                $finished = true;

                break;
            }

            $this->forward($event);
        }

        // like every test, every example ends with a Test\Finished event,
        // even one that ended before it was prepared
        assert($finished);

        $this->dispatchIssues();
        $this->dispatchOutcome(null, $error, $passed, $beforeOutcome);

        $beforeFinished();

        $this->emitter->testFinished($this->test, $numberOfAssertions);
    }

    private function dispatchPreparation(): void
    {
        if ($this->preparationStarted !== null) {
            $this->forward($this->preparationStarted);
        } else {
            $this->emitter->testPreparationStarted($this->test);
        }

        // a property of which no example was prepared, for instance because
        // its generators could not be derived, is not reported as prepared,
        // like a test that ended before it was prepared
        if ($this->prepared !== null) {
            $this->forward($this->prepared);
        }
    }

    private function dispatchIssues(): void
    {
        if ($this->issuesDispatched) {
            return;
        }

        $this->issuesDispatched = true;

        $this->facade->forward($this->issues);
    }

    /**
     * @param ?Closure(): void $beforeOutcome
     */
    private function dispatchOutcome(?AnyEvent $outcome, ?Code\Throwable $error, bool $passed, ?Closure $beforeOutcome): void
    {
        if ($this->outcomeDispatched) {
            return;
        }

        if ($outcome === null && $error === null && !$passed) {
            return;
        }

        $this->outcomeDispatched = true;

        if ($beforeOutcome !== null) {
            $beforeOutcome();
        }

        if ($error !== null) {
            $this->emitter->testErrored($this->test, $error);

            return;
        }

        if ($outcome !== null) {
            $this->forward($outcome);

            return;
        }

        $this->emitter->testPassed($this->test);
    }

    private function forward(AnyEvent $event): void
    {
        $events = new EventCollection;

        $events->add($event);

        $this->facade->forward($events);
    }

    private function isReportedOncePerProperty(AnyEvent $event): bool
    {
        if (!$this->isIssue($event)) {
            return false;
        }

        if ($event instanceof ConsideredRisky) {
            foreach (self::MESSAGES_DECIDED_FOR_THE_PROPERTY as $message) {
                if (str_starts_with($event->message(), $message)) {
                    return false;
                }
            }
        }

        if ($event instanceof ConsideredRisky ||
            $event instanceof PhpunitDeprecationTriggered ||
            $event instanceof PhpunitErrorTriggered ||
            $event instanceof PhpunitNoticeTriggered ||
            $event instanceof PhpunitWarningTriggered) {
            $key = $event::class . ':' . $event->message();

            if (isset($this->reportedMessages[$key])) {
                return false;
            }

            $this->reportedMessages[$key] = true;
        }

        return true;
    }

    private function isIssue(AnyEvent $event): bool
    {
        return $event instanceof ConsideredRisky ||
               $event instanceof DeprecationTriggered ||
               $event instanceof ErrorTriggered ||
               $event instanceof NoticeTriggered ||
               $event instanceof WarningTriggered ||
               $event instanceof PhpDeprecationTriggered ||
               $event instanceof PhpNoticeTriggered ||
               $event instanceof PhpWarningTriggered ||
               $event instanceof PhpunitDeprecationTriggered ||
               $event instanceof PhpunitErrorTriggered ||
               $event instanceof PhpunitNoticeTriggered ||
               $event instanceof PhpunitWarningTriggered;
    }

    private function isOutcome(AnyEvent $event): bool
    {
        return $event instanceof Passed ||
               $event instanceof Failed ||
               $event instanceof Errored ||
               $event instanceof Skipped ||
               $event instanceof MarkedIncomplete;
    }
}
