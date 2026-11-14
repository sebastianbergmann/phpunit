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

use const E_USER_WARNING;
use function implode;
use function ob_get_level;
use function restore_error_handler;
use function set_error_handler;
use function trigger_error;
use LogicException;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Falsification\FormattedCounterexamplesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\ValidPropertiesTest;
use PHPUnit\TestFixture\PropertyBased\StateMachine\CounterMachine;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use RuntimeException;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;

#[CoversClass(ExampleRunner::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ExampleRunnerTest extends TestCase
{
    public function testCreatesTheTestCaseOfAnExampleWithItsArguments(): void
    {
        $example         = $this->runner('testWithExplicitExamples')->example(['small' => 1, 'name' => 'one'], true);
        $propertyExample = $example->propertyExample();

        $this->assertInstanceOf(ValidPropertiesTest::class, $example);
        $this->assertNotNull($propertyExample);
        $this->assertSame([1, 'one'], $propertyExample->arguments());
        $this->assertTrue($propertyExample->failureIsReported());
    }

    #[WithoutErrorHandler]
    public function testRunsAnExampleWithItsEventsCollected(): void
    {
        $runner = $this->runner('testWithoutParameters');

        [$events, $run] = $runner->run($runner->example([]), Facade::emitter(), false);

        $this->assertSame(
            [PreparationStarted::class, Prepared::class, Passed::class, Finished::class],
            $this->classesOf($events),
        );
        $this->assertNull($run->contributedToCodeCoverage());
        $this->assertFalse(ExampleRunner::endedBeforeItWasPrepared($events));
    }

    public function testAnExampleWithoutATestPreparedEventEndedBeforeItWasPrepared(): void
    {
        $this->assertTrue(ExampleRunner::endedBeforeItWasPrepared(new EventCollection));
    }

    public function testGeneratesTheArgumentsOfAnExampleFromASourceOfChoices(): void
    {
        $runner = $this->runner('testWithGeneratorMethodWithOptionalParameter');

        $arguments = $runner->generateArguments($this->generators('testWithGeneratorMethodWithOptionalParameter'), ChoiceSource::replay(new ChoiceSequence([])));

        $this->assertSame(['value' => 0], $arguments);
    }

    public function testGeneratesTheArgumentsOfAnExampleWithinATimeLimit(): void
    {
        $runner = $this->runner('testWithGeneratorMethodWithOptionalParameter');

        $arguments = $runner->generateArgumentsWithinTimeLimit($this->generators('testWithGeneratorMethodWithOptionalParameter'), ChoiceSource::replay(new ChoiceSequence([])), 1);

        $this->assertSame(['value' => 0], $arguments);
    }

    public function testExportsTheArgumentsOfAnExampleAsPhpCode(): void
    {
        $exported = $this->runner('testWithExplicitExamples')->export(['small' => 1, 'name' => 'one'], new Constructions);

        $this->assertSame(['small' => '1', 'name' => "'one'"], $exported);
    }

    #[WithoutErrorHandler]
    public function testReturnsWhatTheCallableThatGeneratesValuesReturnsWithTheEventsThatAreEmittedMeanwhileCollected(): void
    {
        [$facade, $tracer] = $this->sealedFacadeWithTracer();

        $events = $this->eventsWith($tracer->events[0]);

        $value = $this->runner('testWithoutParameters', $facade)->handlingIssues(
            static function () use ($facade, $events): string
            {
                $facade->forward($events);

                return 'value';
            },
            null,
        );

        $this->assertSame('value', $value);
        $this->assertCount(1, $tracer->events);

        $facade->forward($events);

        $this->assertCount(2, $tracer->events);
    }

    #[WithoutErrorHandler]
    public function testStopsCollectingTheEventsWhenTheCallableThatGeneratesValuesRaisesAnException(): void
    {
        [$facade, $tracer] = $this->sealedFacadeWithTracer();

        $exception = new LogicException;

        try {
            $this->runner('testWithoutParameters', $facade)->handlingIssues(
                static fn (): never => throw $exception,
                null,
            );

            $this->fail('The exception was not raised');
        } catch (LogicException $e) {
            $this->assertSame($exception, $e);
        }

        $facade->forward($this->eventsWith($tracer->events[0]));

        $this->assertCount(2, $tracer->events);
    }

    public function testDoesNotTriggerTheTestCaseContextIssuesOfAPropertyThatDoesNotUsePhpunitsErrorHandler(): void
    {
        $this->runner('testWithoutParameters')->triggerTestCaseContextIssues();

        $this->assertTrue(true);
    }

    public function testGeneratesTheArgumentsOfAnExampleAgainFromItsChoices(): void
    {
        [$deriver, $generators] = $this->generatorsOfPropertyWithFormatter('testIsFalsifiedFromFive');

        $arguments = $this->runnerOfPropertyWithFormatter('testIsFalsifiedFromFive')->argumentsGeneratedFrom($generators, new ChoiceSequence([7]), $deriver);

        $this->assertSame(['value' => 7], $arguments());
    }

    #[WithoutErrorHandler]
    public function testFormatsArgumentsWithTheCounterexampleFormatterOfTheProperty(): void
    {
        FormattedCounterexamplesTest::$numberOfCalls = 0;

        $formatted = $this->runnerOfPropertyWithFormatter('testIsFalsifiedFromFive')->format(static fn (): array => ['value' => 7], $this->eventAggregator());

        $this->assertSame('value 7, call 1', $formatted);
    }

    #[WithoutErrorHandler]
    public function testDoesNotGenerateArgumentsForAPropertyWithoutACounterexampleFormatter(): void
    {
        $generated = false;

        $formatted = $this->runnerOfPropertyWithFormatter('testWithoutFormatter')->format(
            static function () use (&$generated): array
            {
                $generated = true;

                return ['value' => 7];
            },
            $this->eventAggregator(),
        );

        $this->assertNull($formatted);
        $this->assertFalse($generated);
    }

    #[WithoutErrorHandler]
    public function testReportsThatTheArgumentsOfTheCounterexampleFormatterCouldNotBeGeneratedInPlaceOfWhatItReturns(): void
    {
        $formatted = $this->runnerOfPropertyWithFormatter('testIsFalsifiedFromFive')->format(static fn (): never => throw new RuntimeException('message'), $this->eventAggregator());

        $this->assertSame(
            'Counterexample formatter ' . FormattedCounterexamplesTest::class . '::countsCalls() was not called because generating its arguments raised RuntimeException: message',
            $formatted,
        );
    }

    #[WithoutErrorHandler]
    public function testReportsTheExceptionThatTheCounterexampleFormatterRaisedInPlaceOfWhatItReturns(): void
    {
        $formatted = $this->runnerOfPropertyWithFormatter('testWithFormatterThatRaisesAnException')->format(static fn (): array => ['value' => 7], $this->eventAggregator());

        $this->assertSame(
            'Counterexample formatter ' . FormattedCounterexamplesTest::class . '::raises() raised RuntimeException: message',
            $formatted,
        );
    }

    #[WithoutErrorHandler]
    public function testReportsThatTheCounterexampleFormatterDidNotReturnAStringInPlaceOfWhatItReturns(): void
    {
        $formatted = $this->runnerOfPropertyWithFormatter('testWithFormatterThatDoesNotReturnAString')->format(static fn (): array => ['value' => 7], $this->eventAggregator());

        $this->assertSame(
            'Counterexample formatter ' . FormattedCounterexamplesTest::class . '::returnsAnInteger() returned int instead of a string',
            $formatted,
        );
    }

    #[WithoutErrorHandler]
    public function testDoesNotReportAnEmptyStringThatTheCounterexampleFormatterReturns(): void
    {
        $formatted = $this->runnerOfPropertyWithFormatter('testWithFormatterThatReturnsAnEmptyString')->format(static fn (): array => ['value' => 7], $this->eventAggregator());

        $this->assertNull($formatted);
    }

    #[WithoutErrorHandler]
    public function testDiscardsTheOutputThatTheCounterexampleFormatterPrints(): void
    {
        $this->expectOutputString('');

        $formatted = $this->runnerOfPropertyWithFormatter('testWithFormatterThatPrintsOutput')->format(static fn (): array => ['value' => 7], $this->eventAggregator());

        $this->assertSame('value 7', $formatted);
    }

    #[WithoutErrorHandler]
    public function testEndsTheOutputBufferThatTheCounterexampleFormatterStartedAndDidNotEnd(): void
    {
        $this->expectOutputString('');

        $level = ob_get_level();

        $formatted = $this->runnerOfPropertyWithFormatter('testWithFormatterThatPrintsOutputIntoBufferThatItDoesNotEnd')->format(static fn (): array => ['value' => 7], $this->eventAggregator());

        $this->assertSame('value 7', $formatted);
        $this->assertSame($level, ob_get_level());
    }

    #[WithoutErrorHandler]
    public function testIgnoresTheIssuesThatTheCounterexampleFormatterTriggersForTheExampleThatWasRunWhenTheProcessEnded(): void
    {
        $issues = [];

        set_error_handler(
            static function (int $level, string $message) use (&$issues): bool
            {
                $issues[] = $message;

                return true;
            },
        );

        try {
            $formatted = $this->runnerOfPropertyWithFormatter('testWithFormatterThatTriggersWarning')->formatIgnoringIssues(static fn (): array => ['value' => 7]);

            trigger_error('after the formatter was called', E_USER_WARNING);
        } finally {
            restore_error_handler();
        }

        $this->assertSame('value 7', $formatted);
        $this->assertSame(['after the formatter was called'], $issues);
    }

    public function testDescribesAGeneratedExampleWithItsArgumentsWhatTheCounterexampleFormatterReturnsForThemAndTheValuesThatItDrew(): void
    {
        FormattedCounterexamplesTest::$numberOfCalls = 0;

        [, $generators] = $this->generatorsOfPropertyWithFormatter('testIsFalsifiedFromFive');

        $runner      = $this->runnerOfPropertyWithFormatter('testIsFalsifiedFromFive');
        $source      = ChoiceSource::replay(new ChoiceSequence([7, 3]));
        $arguments   = $runner->generateArguments($generators, $source);
        $drawnValues = DrawnValues::from($source);

        $drawnValues->draw(Generators::integers(0, 9), 'label');

        $describe = $runner->describerOfGeneratedExample($arguments, $source, $drawnValues, $generators);

        $this->assertSame(
            <<<'EOT'
The PHP process ended while this example of the property was run:

    $value = 7;

    value 7, call 1

    Draw 1 (label): 3

EOT,
            implode("\n", [...$describe()]),
        );
    }

    public function testDescribesAGeneratedExampleWithTheStepsOfItsStateMachine(): void
    {
        [, $generators] = $this->generatorsOfPropertyWithFormatter('testWithoutFormatter');

        $runner      = $this->runnerOfPropertyWithFormatter('testWithoutFormatter');
        $source      = ChoiceSource::replay(new ChoiceSequence([7, 0, 1, 1, 0, 0]));
        $arguments   = $runner->generateArguments($generators, $source);
        $drawnValues = DrawnValues::from($source);

        $drawnValues->runStateMachine(new CounterMachine, 50);

        $describe = $runner->describerOfGeneratedExample($arguments, $source, $drawnValues, $generators);

        $this->assertSame(
            <<<'EOT'
The PHP process ended while this example of the property was run:

    $value = 7;

    $machine->start(value: 0);
    $machine->increment(amount: 1);

EOT,
            implode("\n", [...$describe()]),
        );
    }

    public function testDescribesAnExplicitExampleWithItsArgumentsAndWhatTheCounterexampleFormatterReturnsForThem(): void
    {
        FormattedCounterexamplesTest::$numberOfCalls = 0;

        $describe = $this->runnerOfPropertyWithFormatter('testIsFalsifiedFromFive')->describerOfExplicitExample(['value' => '7'], ['value' => 7]);

        $this->assertSame(
            <<<'EOT'
The PHP process ended while this example of the property was run:

    $value = 7;

    value 7, call 1

EOT,
            implode("\n", [...$describe()]),
        );
    }

    /**
     * @param non-empty-string $methodName
     */
    private function runner(string $methodName, ?Facade $facade = null): ExampleRunner
    {
        $test = new ValidPropertiesTest($methodName);

        if ($facade === null) {
            $facade = Facade::instance();
        }

        return new ExampleRunner(
            $test->valueObjectForEvents(),
            false,
            static fn (): TestCase => new ValidPropertiesTest($methodName),
            new GeneratorResolver(new PropertyDefinitionBuilder()->build(ValidPropertiesTest::class, $methodName)),
            $facade,
            FalsifyingIssues::fromConfiguration(ConfigurationRegistry::get()),
        );
    }

    /**
     * @param non-empty-string $methodName
     *
     * @return array<non-empty-string, Generator\AbstractGenerator<mixed>>
     */
    private function generators(string $methodName): array
    {
        [, $generators] = new GeneratorResolver(new PropertyDefinitionBuilder()->build(ValidPropertiesTest::class, $methodName))->generators(new ValidPropertiesTest($methodName), $this->createStub(Emitter::class));

        return $generators;
    }

    /**
     * @param non-empty-string $methodName
     */
    private function runnerOfPropertyWithFormatter(string $methodName): ExampleRunner
    {
        return new ExampleRunner(
            new FormattedCounterexamplesTest($methodName)->valueObjectForEvents(),
            false,
            static fn (): TestCase => new FormattedCounterexamplesTest($methodName),
            new GeneratorResolver(new PropertyDefinitionBuilder()->build(FormattedCounterexamplesTest::class, $methodName)),
            Facade::instance(),
            FalsifyingIssues::fromConfiguration(ConfigurationRegistry::get()),
        );
    }

    /**
     * @param non-empty-string $methodName
     *
     * @return array{0: Derivation\GeneratorDeriver, 1: array<non-empty-string, Generator\AbstractGenerator<mixed>>}
     */
    private function generatorsOfPropertyWithFormatter(string $methodName): array
    {
        return new GeneratorResolver(new PropertyDefinitionBuilder()->build(FormattedCounterexamplesTest::class, $methodName))->generators(new FormattedCounterexamplesTest($methodName), $this->createStub(Emitter::class));
    }

    private function eventAggregator(): EventAggregator
    {
        return new EventAggregator(Facade::emitter(), Facade::instance(), $this->valueObjectForEvents());
    }

    /**
     * @return list<class-string>
     */
    private function classesOf(EventCollection $events): array
    {
        $classes = [];

        foreach ($events as $event) {
            $classes[] = $event::class;
        }

        return $classes;
    }

    private function eventsWith(Event $event): EventCollection
    {
        $events = new EventCollection;

        $events->add($event);

        return $events;
    }

    /**
     * @return array{0: Facade, 1: object{events: list<Event>}&Tracer}
     */
    private function sealedFacadeWithTracer(): array
    {
        $tracer = new class implements Tracer
        {
            /**
             * @var list<Event>
             */
            public array $events = [];

            public function trace(Event $event): void
            {
                $this->events[] = $event;
            }
        };

        $facade = new Facade;

        $facade->registerTracer($tracer);
        $facade->seal();

        return [$facade, $tracer];
    }
}
