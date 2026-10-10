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

use function array_map;
use Closure;
use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Test\AdditionalInformationProvided;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PrintedUnexpectedOutput;
use PHPUnit\Event\Test\PropertyChecked;
use PHPUnit\Event\Test\PropertyFalsified;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\GarbageCollectionTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\PropertyBased\EventCollector;
use ReflectionClass;

#[CoversClass(EventAggregator::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class EventAggregatorTest extends AbstractEventTestCase
{
    public function testReportsTheEventsOfTheSkeletonWithThePreparationOfTheFirstExample(): void
    {
        $first  = $this->passedExample();
        $second = $this->passedExample(new AdditionalInformationProvided($this->telemetryInfo(), $this->testValueObject(), 'information'));

        $events = $this->dispatch([[$first, true], [$second, true]], $second, null, true, null, 2);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'AdditionalInformationProvided: information', 'Passed', 'PropertyChecked', 'Finished: 2'],
            $this->describe($events),
        );

        $this->assertSame($first->asArray()[0], $events[0]);
        $this->assertSame($first->asArray()[1], $events[1]);
        $this->assertSame($second->asArray()[2], $events[2]);
        $this->assertSame($second->asArray()[3], $events[3]);
    }

    public function testReportsTheIssuesOfAllCheckedExamplesOnceTheSkeletonIsPrepared(): void
    {
        $first = $this->passedExample(
            $this->warning('warning'),
            $this->risky('risky'),
            $this->phpunitWarning('PHPUnit warning'),
        );

        $second = $this->passedExample(
            $this->warning('warning'),
            $this->risky('risky'),
            $this->phpunitWarning('PHPUnit warning'),
            $this->risky('other risky'),
        );

        $events = $this->dispatch([[$first, true], [$second, true]], $second, null, true, null, 2);

        $this->assertSame(
            [
                'PreparationStarted',
                'Prepared',
                'WarningTriggered: warning',
                'ConsideredRisky: risky',
                'PhpunitWarningTriggered: PHPUnit warning',
                'WarningTriggered: warning',
                'ConsideredRisky: other risky',
                'Passed',
                'PropertyChecked',
                'Finished: 2',
            ],
            $this->describe($events),
        );
    }

    public function testReportsTheIssuesOfExamplesThatWereNotCheckedButDoesNotConsiderThemRisky(): void
    {
        $rejected = $this->example(
            $this->preparationStarted(),
            $this->prepared(),
            $this->warning('warning of rejected example'),
            $this->risky('risky rejected example'),
            $this->finished(0),
        );

        $checked = $this->passedExample();

        $events = $this->dispatch([[$rejected, false], [$checked, true]], $checked, null, true, null, 1);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'WarningTriggered: warning of rejected example', 'Passed', 'PropertyChecked', 'Finished: 1'],
            $this->describe($events),
        );

        $this->assertSame($rejected->asArray()[0], $events[0]);
    }

    public function testReportsTheGarbageCollectionsOfAllExamplesAndShrinkingAttemptsOnceTheSkeletonIsPrepared(): void
    {
        $checked  = $this->passedExample($this->garbageCollection());
        $rejected = $this->example($this->preparationStarted(), $this->prepared(), $this->garbageCollection(), $this->finished(0));
        $attempt  = $this->example($this->preparationStarted(), $this->prepared(), $this->garbageCollection(), $this->finished(0));

        $events = $this->dispatch([[$checked, true], [$rejected, false]], $checked, null, true, null, 1, [$attempt]);

        $this->assertSame(
            [
                'PreparationStarted',
                'Prepared',
                'GarbageCollectionTriggered',
                'GarbageCollectionTriggered',
                'GarbageCollectionTriggered',
                'Passed',
                'PropertyChecked',
                'Finished: 1',
            ],
            $this->describe($events),
        );
    }

    public function testReportsTheAdditionalInformationOfAllCheckedExamplesOnceTheSkeletonIsPrepared(): void
    {
        $first    = $this->passedExample($this->additionalInformation('first'));
        $rejected = $this->example($this->preparationStarted(), $this->prepared(), $this->additionalInformation('rejected'), $this->finished(0));
        $second   = $this->passedExample($this->additionalInformation('second'));

        $events = $this->dispatch([[$first, true], [$rejected, false], [$second, true]], $second, null, true, null, 2);

        $this->assertSame(
            [
                'PreparationStarted',
                'Prepared',
                'AdditionalInformationProvided: first',
                'AdditionalInformationProvided: second',
                'Passed',
                'PropertyChecked',
                'Finished: 2',
            ],
            $this->describe($events),
        );
    }

    public function testReportsTheOutputOfAllCheckedExamplesAsOneEventAfterTheOutcome(): void
    {
        $first    = $this->passedExample($this->printedOutput("first\n"));
        $rejected = $this->example($this->preparationStarted(), $this->prepared(), $this->printedOutput("rejected\n"), $this->finished(0));
        $second   = $this->passedExample($this->printedOutput("second\n"));

        $events = $this->dispatch([[$first, true], [$rejected, false], [$second, true]], $second, null, true, null, 2);

        $this->assertSame(
            [
                'PreparationStarted',
                'Prepared',
                'Passed',
                "PrintedUnexpectedOutput: first\nsecond\n",
                'PropertyChecked',
                'Finished: 2',
            ],
            $this->describe($events),
        );
    }

    public function testDoesNotReportOutputWhenNoExamplePrintedAny(): void
    {
        $example = $this->passedExample();

        $events = $this->dispatch([[$example, true]], $example, null, true, null, 1);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'Passed', 'PropertyChecked', 'Finished: 1'],
            $this->describe($events),
        );
    }

    public function testDispatchesTheEventsThatPrecedeTheOutcomeOfAFalsifiedProperty(): void
    {
        $example = $this->example(
            $this->preparationStarted(),
            $this->prepared(),
            new Failed($this->telemetryInfo(), $this->testValueObject(), ThrowableBuilder::from(new Exception('failure')), null),
            $this->finished(1),
        );

        $events = $this->dispatch([[$example, true]], $example, null, false, $this->emitPropertyFalsified(...), 1);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'PropertyFalsified', 'Failed', 'PropertyChecked', 'Finished: 1'],
            $this->describe($events),
        );
    }

    public function testDispatchesEveryOutcomeOfTheSkeleton(): void
    {
        $example = $this->example(
            $this->preparationStarted(),
            $this->prepared(),
            new Failed($this->telemetryInfo(), $this->testValueObject(), ThrowableBuilder::from(new Exception('failure')), null),
            new Errored($this->telemetryInfo(), $this->testValueObject(), ThrowableBuilder::from(new Exception('error in destructor of test double'))),
            $this->finished(1),
        );

        $events = $this->dispatch([[$example, true]], $example, null, false, $this->emitPropertyFalsified(...), 1);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'PropertyFalsified', 'Failed', 'Errored: error in destructor of test double', 'PropertyChecked', 'Finished: 1'],
            $this->describe($events),
        );
    }

    public function testReplacesTheOutcomeOfTheSkeletonWithAnError(): void
    {
        $example = $this->passedExample();

        $events = $this->dispatch([[$example, true]], $example, $this->error(), false, $this->emitPropertyFalsified(...), 1);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'PropertyFalsified', 'Errored: error', 'PropertyChecked', 'Finished: 1'],
            $this->describe($events),
        );
    }

    public function testDispatchesAnErrorForASkeletonWithoutAnOutcomeBeforeItFinishes(): void
    {
        $example = $this->example(
            $this->preparationStarted(),
            $this->prepared(),
            $this->finished(0),
        );

        $events = $this->dispatch([[$example, true]], $example, $this->error(), false, $this->emitPropertyFalsified(...), 0);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'PropertyFalsified', 'Errored: error', 'PropertyChecked', 'Finished: 0'],
            $this->describe($events),
        );
    }

    public function testDoesNotDispatchAnOutcomeForASkeletonWithoutAnOutcomeWhenThePropertyDidNotPass(): void
    {
        $example = $this->example(
            $this->preparationStarted(),
            $this->prepared(),
            $this->finished(0),
        );

        $events = $this->dispatch([[$example, true]], $example, null, false, null, 0);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'PropertyChecked', 'Finished: 0'],
            $this->describe($events),
        );
    }

    public function testDispatchesTheEventsOfASkeletonThatWasNotPreparedWithoutFinishingIt(): void
    {
        $example = $this->example(
            $this->preparationStarted(),
            $this->warning('warning'),
            new Errored($this->telemetryInfo(), $this->testValueObject(), ThrowableBuilder::from(new Exception('error in setUp()'))),
        );

        $events = $this->dispatch([[$example, true]], $example, null, false, null, 0);

        $this->assertSame(
            ['PreparationStarted', 'WarningTriggered: warning', 'Errored: error in setUp()', 'PropertyChecked'],
            $this->describe($events),
        );
    }

    public function testDispatchesTheIssuesOfASkeletonWithoutEventsThatAreDispatchedAfterThem(): void
    {
        $example = $this->example(
            $this->preparationStarted(),
            $this->warning('warning'),
        );

        $events = $this->dispatch([[$example, true]], $example, null, false, null, 0);

        $this->assertSame(
            ['PreparationStarted', 'WarningTriggered: warning', 'PropertyChecked'],
            $this->describe($events),
        );
    }

    public function testReportsAPropertyForWhichNoExampleRanAsAPassedTest(): void
    {
        $events = $this->dispatch([], null, null, true, null, 0);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'Passed', 'PropertyChecked', 'Finished: 0'],
            $this->describe($events),
        );
    }

    public function testReportsAnErrorForAPropertyForWhichNoExampleRanWithThePreparationOfTheFirstExample(): void
    {
        $rejected = $this->example(
            $this->preparationStarted(),
            $this->prepared(),
            $this->finished(0),
        );

        $events = $this->dispatch([[$rejected, false]], null, $this->error(), false, null, 0);

        $this->assertSame(
            ['PreparationStarted', 'Prepared', 'Errored: error', 'PropertyChecked', 'Finished: 0'],
            $this->describe($events),
        );

        $this->assertSame($rejected->asArray()[0], $events[0]);
        $this->assertSame($rejected->asArray()[1], $events[1]);
    }

    /**
     * @param list<array{EventCollection, bool}> $examples
     * @param ?Closure(): void                   $beforeOutcome
     * @param non-negative-int                   $numberOfAssertions
     * @param list<EventCollection>              $shrinkingAttempts
     *
     * @return list<Event>
     */
    private function dispatch(array $examples, ?EventCollection $skeleton, ?Throwable $error, bool $passed, ?Closure $beforeOutcome, int $numberOfAssertions, array $shrinkingAttempts = []): array
    {
        return EventCollector::collect(
            function () use ($examples, $skeleton, $error, $passed, $beforeOutcome, $numberOfAssertions, $shrinkingAttempts): void
            {
                $aggregator = new EventAggregator(EventFacade::emitter(), EventFacade::instance(), $this->testValueObject());

                foreach ($examples as [$events, $checked]) {
                    $aggregator->record($events, $checked);
                }

                foreach ($shrinkingAttempts as $events) {
                    $aggregator->recordShrinkingAttempt($events);
                }

                $aggregator->dispatch(
                    $skeleton,
                    $error,
                    $passed,
                    $beforeOutcome,
                    function (): void
                    {
                        EventFacade::emitter()->testPropertyChecked($this->testValueObject(), 1, 1, 0);
                    },
                    $numberOfAssertions,
                );
            },
        )->asArray();
    }

    /**
     * @param list<Event> $events
     *
     * @return list<string>
     */
    private function describe(array $events): array
    {
        return array_map(
            static function (Event $event): string
            {
                return match (true) {
                    $event instanceof PreparationStarted            => 'PreparationStarted',
                    $event instanceof Prepared                      => 'Prepared',
                    $event instanceof Passed                        => 'Passed',
                    $event instanceof Failed                        => 'Failed',
                    $event instanceof Errored                       => 'Errored: ' . $event->throwable()->message(),
                    $event instanceof Finished                      => 'Finished: ' . $event->numberOfAssertionsPerformed(),
                    $event instanceof WarningTriggered              => 'WarningTriggered: ' . $event->message(),
                    $event instanceof PhpunitWarningTriggered       => 'PhpunitWarningTriggered: ' . $event->message(),
                    $event instanceof ConsideredRisky               => 'ConsideredRisky: ' . $event->message(),
                    $event instanceof PropertyChecked               => 'PropertyChecked',
                    $event instanceof PropertyFalsified             => 'PropertyFalsified',
                    $event instanceof AdditionalInformationProvided => 'AdditionalInformationProvided: ' . $event->additionalInformation(),
                    $event instanceof PrintedUnexpectedOutput       => 'PrintedUnexpectedOutput: ' . $event->output(),
                    default                                         => new ReflectionClass($event)->getShortName(),
                };
            },
            $events,
        );
    }

    private function emitPropertyFalsified(): void
    {
        EventFacade::emitter()->testPropertyFalsified($this->testValueObject(), 1, 1, 0, 0, false, false, [], [], null);
    }

    private function passedExample(Event ...$events): EventCollection
    {
        $example = $this->example($this->preparationStarted(), $this->prepared(), ...$events);

        $example->add(
            new Passed($this->telemetryInfo(), $this->testValueObject()),
            $this->finished(1),
        );

        return $example;
    }

    private function example(Event ...$events): EventCollection
    {
        $collection = new EventCollection;

        $collection->add(...$events);

        return $collection;
    }

    private function preparationStarted(): PreparationStarted
    {
        return new PreparationStarted($this->telemetryInfo(), $this->testValueObject());
    }

    private function prepared(): Prepared
    {
        return new Prepared($this->telemetryInfo(), $this->testValueObject());
    }

    /**
     * @param non-negative-int $numberOfAssertions
     */
    private function finished(int $numberOfAssertions): Finished
    {
        return new Finished($this->telemetryInfo(), $this->testValueObject(), $numberOfAssertions);
    }

    /**
     * @param non-empty-string $additionalInformation
     */
    private function additionalInformation(string $additionalInformation): AdditionalInformationProvided
    {
        return new AdditionalInformationProvided($this->telemetryInfo(), $this->testValueObject(), $additionalInformation);
    }

    /**
     * @param non-empty-string $output
     */
    private function printedOutput(string $output): PrintedUnexpectedOutput
    {
        return new PrintedUnexpectedOutput($this->telemetryInfo(), $output);
    }

    private function garbageCollection(): GarbageCollectionTriggered
    {
        return new GarbageCollectionTriggered($this->telemetryInfo());
    }

    private function warning(string $message): WarningTriggered
    {
        return new WarningTriggered($this->telemetryInfo(), $this->testValueObject(), $message, 'file.php', 1, false, false);
    }

    /**
     * @param non-empty-string $message
     */
    private function risky(string $message): ConsideredRisky
    {
        return new ConsideredRisky($this->telemetryInfo(), $this->testValueObject(), $message);
    }

    /**
     * @param non-empty-string $message
     */
    private function phpunitWarning(string $message): PhpunitWarningTriggered
    {
        return new PhpunitWarningTriggered($this->telemetryInfo(), $this->testValueObject(), $message, false);
    }

    private function error(): Throwable
    {
        return ThrowableBuilder::from(new Exception('error'));
    }
}
