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
use function rtrim;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event as AnyEvent;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Test\AdditionalInformationProvided;
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
use PHPUnit\Event\Test\PrintedUnexpectedOutput;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\GarbageCollectionTriggered;
use PHPUnit\Util\PropertyFalsifiedToStringMapper;

/**
 * Aggregates the events of the examples of a property, which are collected
 * rather than dispatched, into the events that are reported for the property.
 *
 * Exactly one sequence of events from Test\PreparationStarted to
 * Test\Finished is reported for a property: the one of a single example, the
 * skeleton, whose Test\PreparationStarted and Test\Prepared events are
 * replaced with those of the first example, so that the duration of the
 * property covers all of its examples. The issues and the
 * Test\ConsideredRisky events of all examples that were checked are reported
 * within that sequence, those of the skeleton included, and so is the output
 * that they printed, as one Test\PrintedUnexpectedOutput event. The issues of
 * the examples that were rejected are reported as well, and so are the
 * TestRunner\GarbageCollectionTriggered events of all examples, those of the
 * shrinking attempts included. A Test\ConsideredRisky event and an issue of
 * PHPUnit's own are reported once per message, and the number of assertions
 * is the one that is passed in.
 *
 * Only the Test\AdditionalInformationProvided events of the skeleton are
 * reported, and only for a property that did not pass: the additional
 * information is the one that was provided for the example whose outcome is
 * reported, the minimal counterexample, for instance. For a property that
 * passed, it would be the additional information of an arbitrary example.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class EventAggregator
{
    private readonly Event\Emitter $emitter;
    private readonly Event\Facade $facade;
    private readonly Code\Test $test;
    private ?PreparationStarted $preparationStarted = null;
    private ?Prepared $prepared                     = null;

    /**
     * The issues, the Test\ConsideredRisky events, and the
     * TestRunner\GarbageCollectionTriggered events of the examples.
     */
    private EventCollection $eventsOfExamples;

    /**
     * The output that the examples printed.
     */
    private string $output = '';

    /**
     * @var array<non-empty-string, true>
     */
    private array $reportedMessages          = [];
    private bool $eventsOfExamplesDispatched = false;
    private bool $outcomeDispatched          = false;

    public function __construct(Event\Emitter $emitter, Event\Facade $facade, Code\Test $test)
    {
        $this->emitter          = $emitter;
        $this->facade           = $facade;
        $this->test             = $test;
        $this->eventsOfExamples = new EventCollection;
    }

    /**
     * Records the events of an example. Only the Test\PreparationStarted and
     * Test\Prepared events, the issues, and the TestRunner\GarbageCollectionTriggered
     * events of an example that was not checked, because it was rejected, are
     * kept.
     *
     * The events of the skeleton must have been recorded before they are
     * dispatched.
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

            if ($event instanceof GarbageCollectionTriggered) {
                $this->eventsOfExamples->add($event);

                continue;
            }

            // the issues that an example triggered before it was rejected are
            // reported, like those that are triggered while the arguments of
            // an example that is rejected are generated, but an example that
            // was rejected is not considered risky
            if (!$checked) {
                if (!$event instanceof ConsideredRisky && $this->isReportedOncePerProperty($event)) {
                    $this->eventsOfExamples->add($event);
                }

                continue;
            }

            if ($event instanceof PrintedUnexpectedOutput) {
                $this->output .= $event->output();

                continue;
            }

            if ($this->isReportedOncePerProperty($event)) {
                $this->eventsOfExamples->add($event);
            }
        }
    }

    /**
     * Records a PHPUnit warning for the property, which is reported with the
     * issues of its examples: like these, a message is reported once per
     * property, however often it is recorded.
     *
     * @param non-empty-string $message
     */
    public function recordPhpunitWarning(string $message): void
    {
        $this->facade->startCollectingEvents();

        try {
            $this->emitter->testTriggeredPhpunitWarning($this->test, $message);
        } finally {
            $this->record($this->facade->stopCollectingEvents(), true);
        }
    }

    /**
     * Records a PHPUnit warning because the generators of the property did
     * not generate the same arguments and values from the same choices: its
     * examples can then be generated more than once, and its counterexamples
     * cannot be replayed and shrunk reliably.
     */
    public function recordThatGeneratorsAreNondeterministic(): void
    {
        $this->recordPhpunitWarning(
            'The generators of this property did not generate the same values from the same choices, so its examples may be generated more than once and its counterexamples may not be reproducible; values must not depend on anything but the choices they are generated from',
        );
    }

    /**
     * Records the events of a shrinking attempt, of which only the
     * TestRunner\GarbageCollectionTriggered events are kept.
     */
    public function recordShrinkingAttempt(EventCollection $events): void
    {
        foreach ($events as $event) {
            if ($event instanceof GarbageCollectionTriggered) {
                $this->eventsOfExamples->add($event);
            }
        }
    }

    /**
     * Dispatches the events that are reported for the property.
     *
     * @param ?EventCollection     $skeleton           the events of the example whose sequence of events is reported, null when no example ran to its end
     * @param ?Code\Throwable      $error              when not null, replaces the outcome of the skeleton with a Test\Errored event
     * @param bool                 $passed             whether the property passed: a Test\Passed event is dispatched when the skeleton has no outcome, and the additional information of the skeleton is not reported
     * @param ?FalsificationReport $falsification      how the property was falsified, which is reported with a Test\PropertyFalsified event before the outcome and as part of its failure or error, null when it was not falsified
     * @param Closure(): void      $beforeFinished     dispatches events that precede the Test\Finished event
     * @param non-negative-int     $numberOfAssertions
     */
    public function dispatch(?EventCollection $skeleton, ?Code\Throwable $error, bool $passed, ?FalsificationReport $falsification, Closure $beforeFinished, int $numberOfAssertions): void
    {
        if ($skeleton === null) {
            $this->dispatchPreparation();
            $this->dispatchEventsOfExamples();
            $this->dispatchOutcome(null, $error, $passed, $falsification);
            $this->dispatchOutput();

            $beforeFinished();

            $this->emitter->testFinished($this->test, $numberOfAssertions);

            return;
        }

        $finished = false;

        foreach ($skeleton as $event) {
            if ($event instanceof PreparationStarted) {
                if ($this->preparationStarted !== null) {
                    $this->forward($this->preparationStarted);
                } else {
                    $this->forward($event);
                }

                continue;
            }

            if ($event instanceof Prepared) {
                if ($this->prepared !== null) {
                    $this->forward($this->prepared);
                } else {
                    $this->forward($event);
                }

                $this->dispatchEventsOfExamples();

                continue;
            }

            // these events were recorded with those of the other examples
            if ($this->isIssue($event) ||
                $event instanceof PrintedUnexpectedOutput ||
                $event instanceof GarbageCollectionTriggered) {
                continue;
            }

            if ($event instanceof AdditionalInformationProvided) {
                if (!$passed) {
                    $this->forward($event);
                }

                continue;
            }

            if ($this->isOutcome($event)) {
                $this->dispatchEventsOfExamples();
                $this->dispatchOutcome($event, $error, $passed, $falsification);

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

        $this->dispatchEventsOfExamples();
        $this->dispatchOutcome(null, $error, $passed, $falsification);
        $this->dispatchOutput();

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

    private function dispatchEventsOfExamples(): void
    {
        if ($this->eventsOfExamplesDispatched) {
            return;
        }

        $this->eventsOfExamplesDispatched = true;

        $this->facade->forward($this->eventsOfExamples);
    }

    private function dispatchOutput(): void
    {
        if ($this->output === '') {
            return;
        }

        $this->emitter->testPrintedUnexpectedOutput($this->output);
    }

    private function dispatchOutcome(?AnyEvent $outcome, ?Code\Throwable $error, bool $passed, ?FalsificationReport $falsification): void
    {
        if ($this->outcomeDispatched) {
            // a later outcome of the skeleton, for instance a failure in
            // tearDown() after the test method failed, is dispatched as it
            // is, like it is for a test
            if ($outcome !== null) {
                $this->forward($outcome);
            }

            return;
        }

        if ($outcome === null && $error === null && !$passed) {
            return;
        }

        $this->outcomeDispatched = true;

        $falsified = null;

        if ($falsification !== null) {
            $this->emitter->testPropertyFalsified(
                $this->test,
                $falsification->seed(),
                $falsification->numberOfExamplesChecked(),
                $falsification->numberOfShrinkingAttempts(),
                $falsification->numberOfShrinkingSteps(),
                $falsification->shrinkingBudgetExhausted(),
                $falsification->shrinkingInterrupted(),
                $falsification->counterexample(),
                $falsification->otherFalsifications(),
                $falsification->numberOfReplays(),
                $falsification->numberOfReproductions(),
            );

            $falsified = PropertyFalsifiedToStringMapper::map($falsification);
        }

        if ($error !== null) {
            $this->emitter->testErrored($this->test, self::withFalsified($error, $falsified));

            return;
        }

        // an example that falsified the property by triggering an issue
        // passed, so the failure of the property replaces its outcome
        if ($falsification !== null && $falsification->failure() !== null) {
            $this->emitter->testFailed($this->test, self::withFalsified($falsification->failure(), $falsified), null);

            return;
        }

        if ($outcome !== null) {
            $this->forward(self::outcomeWithFalsified($outcome, $falsified));

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

    /**
     * How the property was falsified, with its counterexample, is part of the
     * failure or error of the property, so that every printer and logger that
     * reports the failure or error reports it, too, below the message and
     * above the stack trace.
     */
    private static function outcomeWithFalsified(AnyEvent $outcome, ?string $falsified): AnyEvent
    {
        if ($falsified === null) {
            return $outcome;
        }

        if ($outcome instanceof Failed) {
            $comparisonFailure = null;

            if ($outcome->hasComparisonFailure()) {
                $comparisonFailure = $outcome->comparisonFailure();
            }

            return new Failed(
                $outcome->telemetryInfo(),
                $outcome->test(),
                self::withFalsified($outcome->throwable(), $falsified),
                $comparisonFailure,
            );
        }

        if ($outcome instanceof Errored) {
            return new Errored(
                $outcome->telemetryInfo(),
                $outcome->test(),
                self::withFalsified($outcome->throwable(), $falsified),
            );
        }

        return $outcome;
    }

    private static function withFalsified(Code\Throwable $throwable, ?string $falsified): Code\Throwable
    {
        if ($falsified === null) {
            return $throwable;
        }

        $previous = null;

        if ($throwable->hasPrevious()) {
            $previous = $throwable->previous();
        }

        return new Code\Throwable(
            $throwable->className(),
            $throwable->message() . "\n\n" . rtrim($falsified),
            $throwable->description() . "\n" . $falsified,
            $throwable->stackTrace(),
            $previous,
        );
    }
}
