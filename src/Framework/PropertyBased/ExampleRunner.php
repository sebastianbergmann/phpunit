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

use function array_values;
use function assert;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ExampleRun;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\TimeLimit\TimeLimitExceededException;
use ReflectionMethod;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use Throwable;

/**
 * Builds and runs the examples of a property: it creates the test case of an
 * example, generates its arguments from a source of choices, runs it with its
 * events collected, handles the issues that are triggered while values are
 * generated as issues of the property, and exports arguments as PHP code.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExampleRunner
{
    private Event\Code\TestMethod $test;
    private bool $usesErrorHandler;

    /**
     * @var Closure(): TestCase
     */
    private Closure $exampleFactory;
    private GeneratorResolver $generatorResolver;
    private Event\Facade $facade;
    private FalsifyingIssues $falsifyingIssues;

    /**
     * The example of a property that a test case that example() created is.
     */
    public static function propertyExampleOf(TestCase $example): PropertyExample
    {
        $propertyExample = $example->propertyExample();

        assert($propertyExample !== null);

        return $propertyExample;
    }

    /**
     * Whether an example ended before it was prepared, which its events tell
     * because the Test\Prepared event is emitted once the hook methods that
     * are called before the test method have been called.
     */
    public static function endedBeforeItWasPrepared(EventCollection $exampleEvents): bool
    {
        foreach ($exampleEvents as $event) {
            if ($event instanceof Prepared) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool                $usesErrorHandler whether PHPUnit's error handler is used for the property
     * @param Closure(): TestCase $exampleFactory   creates the test case of an example
     */
    public function __construct(Event\Code\TestMethod $test, bool $usesErrorHandler, Closure $exampleFactory, GeneratorResolver $generatorResolver, Event\Facade $facade, FalsifyingIssues $falsifyingIssues)
    {
        $this->falsifyingIssues  = $falsifyingIssues;
        $this->test              = $test;
        $this->usesErrorHandler  = $usesErrorHandler;
        $this->exampleFactory    = $exampleFactory;
        $this->generatorResolver = $generatorResolver;
        $this->facade            = $facade;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     * @param bool                           $failureIsReported whether the failure or error of the example is the one that is reported for the property, which it is for the final replay of the minimal counterexample and for an explicit example
     * @param ?DrawnValues                   $drawnValues       from which the example draws values with draw(), null for an explicit example
     */
    public function example(array $arguments, bool $failureIsReported = false, ?DrawnValues $drawnValues = null): TestCase
    {
        $example = ($this->exampleFactory)();

        $example->setPropertyExample(new PropertyExample(array_values($arguments), $failureIsReported, $drawnValues));

        return $example;
    }

    /**
     * Runs an example with its events collected instead of dispatched, with
     * the test runner rather than TestCase::run(), which would resolve
     * dependencies that a property cannot have. TestCase::run() also emits
     * the Test\PreparationStarted event of a test, so it is emitted here:
     * like every test, every example has exactly one sequence of events from
     * Test\PreparationStarted to Test\Finished.
     *
     * @param ?positive-int      $timeLimit the time limit of the example in seconds, null for the configured one
     * @param ?Closure(): string $describe  describes the example, which is printed when the PHP process ends while the example is run, because of a fatal error, for instance
     *
     * @throws Throwable
     *
     * @return array{0: EventCollection, 1: ExampleRun}
     */
    public function run(TestCase $example, Event\Emitter $emitter, bool $collectCodeCoverage, ?int $timeLimit = null, ?Closure $describe = null): array
    {
        $run = null;

        $events = $this->facade->collectingEvents(
            static function () use ($example, $emitter, $collectCodeCoverage, $timeLimit, $describe, &$run): void
            {
                $emitter->testPreparationStarted(
                    $example->valueObjectForEvents(),
                );

                $run = new TestRunner($emitter)->runExampleOfProperty($example, $collectCodeCoverage, $timeLimit, $describe);
            },
        );

        assert($run instanceof ExampleRun);

        return [$events, $run];
    }

    /**
     * How an example that was run falsified the property, null when it did
     * not: by failing, erroring, or exceeding its time limit, or else by
     * triggering an issue for which the test run fails.
     *
     * An example that was rejected, skipped, marked incomplete, or aborted
     * because the deadline of the test run has passed did not check the
     * property, so the issues that it triggered do not falsify the property.
     *
     * @param EventCollection $exampleEvents the events of the example, which run() returned
     */
    public function falsificationOf(TestCase $example, EventCollection $exampleEvents): ?Falsification
    {
        $falsification = Falsification::of($example);

        if ($falsification !== null) {
            return $falsification;
        }

        $propertyExample = self::propertyExampleOf($example);

        if ($propertyExample->wasRejected() ||
            $propertyExample->throwableThatEndedIt() instanceof TimeLimitExceededException ||
            $example->status()->isSkipped() ||
            $example->status()->isIncomplete()) {
            return null;
        }

        return $this->falsifyingIssues->falsificationBy($exampleEvents);
    }

    /**
     * Calls $callable, which generates values, with the issues that are
     * triggered meanwhile handled as issues of the property, and returns what
     * it returns: the issues that are triggered by the constructor of a class
     * whose objects are generated or by a closure that is passed to map() or
     * filter(), for instance.
     *
     * The events for these issues are recorded with $events, also when
     * $callable raises an exception, because the example is rejected, for
     * instance. They are not recorded when $events is null, as for a shrinking
     * attempt.
     *
     * @template T
     *
     * @param Closure(): T $callable
     *
     * @throws Throwable
     *
     * @return T
     */
    public function handlingIssues(Closure $callable, ?EventAggregator $events): mixed
    {
        $test   = $this->test;
        $facade = $this->facade;

        $facade->startCollectingEvents();

        if ($this->usesErrorHandler) {
            ErrorHandler::instance()->enableForTestNotOnCallStack($test);
        }

        try {
            return $callable();
        } finally {
            ErrorHandler::instance()->disable();

            $issues = $facade->stopCollectingEvents();

            $events?->record($issues, true);
        }
    }

    /**
     * Reports the issues that were triggered in the test case context of the
     * property, while the test suite was built, for instance, once for the
     * property rather than for each of its examples.
     *
     * @throws Throwable when an error handler that was registered before PHPUnit's own turned one of these issues into an exception
     */
    public function triggerTestCaseContextIssues(): void
    {
        if (!$this->usesErrorHandler) {
            return;
        }

        $throwable = ErrorHandler::instance()->triggerTestCaseContextIssuesOfProperty($this->test);

        if ($throwable !== null) {
            throw $throwable;
        }
    }

    /**
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     *
     * @throws ExampleRejectedException
     *
     * @return array<non-empty-string, mixed>
     */
    public function generateArguments(array $generators, ChoiceSource $source): array
    {
        $arguments = [];

        foreach ($generators as $parameterName => $generator) {
            $arguments[$parameterName] = $generator->generate($source);
        }

        return $arguments;
    }

    /**
     * Generates the arguments of an example that is run on its own, which
     * is aborted when it exceeds the time limit of the example, unless the
     * time limit cannot be enforced.
     *
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     * @param positive-int                                      $timeLimit
     *
     * @throws ExampleRejectedException
     * @throws TimeoutException
     *
     * @return array<non-empty-string, mixed>
     */
    public function generateArgumentsWithinTimeLimit(array $generators, ChoiceSource $source, int $timeLimit): array
    {
        if (!TestRunner::canTimeLimitBeEnforced()) {
            // @codeCoverageIgnoreStart
            return $this->generateArguments($generators, $source);
            // @codeCoverageIgnoreEnd
        }

        $arguments = [];

        new Invoker()->invoke(
            function () use ($generators, $source, &$arguments): void
            {
                $arguments = $this->generateArguments($generators, $source);
            },
            [],
            $timeLimit,
        );

        return $arguments;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     * @param Constructions                  $constructions the objects that generators created with new while the arguments were generated
     *
     * @return array<non-empty-string, string>
     */
    public function export(array $arguments, Constructions $constructions): array
    {
        $exporter = $this->exporter($constructions);
        $exported = [];

        foreach ($arguments as $parameterName => $argument) {
            $exported[$parameterName] = $exporter->export($argument);
        }

        return $exported;
    }

    /**
     * @param Constructions $constructions the objects that generators created with new while the values to be exported were generated
     */
    public function exporter(Constructions $constructions): CounterexampleExporter
    {
        $definition = $this->generatorResolver->definition();

        assert($definition !== null);

        return new CounterexampleExporter(
            NameScope::forMethod(new ReflectionMethod($definition->className(), $definition->methodName())),
            $constructions,
        );
    }
}
