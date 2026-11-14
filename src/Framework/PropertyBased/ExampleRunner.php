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
use function get_debug_type;
use function is_string;
use function ob_end_clean;
use function ob_get_level;
use function ob_start;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ExampleRun;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\TimeLimit\TimeLimitExceededException;
use PHPUnit\Util\PropertyFalsifiedToStringMapper;
use ReflectionMethod;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
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
     * @param ?positive-int                $timeLimit the time limit of the example in seconds, null for the configured one
     * @param ?Closure(): iterable<string> $describe  describes the example, which is printed when the PHP process ends while the example is run, because of a fatal error, for instance
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
     * Generates the arguments of an example again from its choices, for the
     * counterexample formatter, which is not called with the arguments that
     * an example is run with.
     *
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     *
     * @return Closure(): array<non-empty-string, mixed>
     */
    public function argumentsGeneratedFrom(array $generators, ChoiceSequence $sequence, GeneratorDeriver $deriver): Closure
    {
        return fn (): array => $this->generateArguments(
            $generators,
            ChoiceSource::replay($sequence, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver),
        );
    }

    /**
     * What the method that is specified with #[CounterexampleFormatter] or
     * #[CounterexampleFormatterExternal] returns for the arguments of a
     * counterexample that is reported, null when no such method is specified
     * or when it returns an empty string.
     *
     * The formatter is called with arguments of its own, which $arguments
     * returns, rather than with the ones that an example is run with: an
     * example cannot change what is formatted, and a formatter that changes
     * the state of an argument, or consumes an iterator, cannot change what
     * an example is run with. $arguments is only called when a formatter is
     * specified.
     *
     * The issues that the formatter triggers are handled as issues of the
     * property and recorded with $events, and the output that it prints is
     * discarded.
     *
     * A formatter that raises an exception or does not return a string does
     * not keep the counterexample from being reported, and neither does an
     * exception that is raised while its arguments are generated: what went
     * wrong is reported in place of what the formatter should have returned.
     *
     * @param Closure(): array<non-empty-string, mixed> $arguments
     *
     * @return ?non-empty-string
     */
    public function format(Closure $arguments, EventAggregator $events): ?string
    {
        return $this->callFormatter(
            fn (): array => $this->handlingIssues($arguments, null),
            fn (Closure $formatter): mixed => $this->handlingIssues($formatter, $events),
        );
    }

    /**
     * What the counterexample formatter returns for the arguments of the
     * example that was run when the PHP process ended, see format(). The
     * issues that the formatter triggers are ignored: the events of the
     * property are not dispatched anymore, and PHP would print the issues
     * into the description of the example.
     *
     * @param Closure(): array<non-empty-string, mixed> $arguments
     *
     * @return ?non-empty-string
     */
    public function formatIgnoringIssues(Closure $arguments): ?string
    {
        return $this->callFormatter(
            static fn (): array => self::ignoringIssues($arguments),
            static fn (Closure $formatter): mixed => self::ignoringIssues($formatter),
        );
    }

    /**
     * Describes a generated example for the case that the PHP process ends
     * while it is run, because of a fatal error, for instance: an example
     * that ends the process is neither reported for the property nor shrunk,
     * so this is the only report of its arguments, of the values that it
     * drew, and of the steps of its state machine. The ones that are not
     * reported otherwise are only exported then, in the state in which the
     * example left them.
     *
     * The example is described in parts, of which each is printed before the
     * next one is worked out: the arguments are printed before the
     * counterexample formatter is called, which can end the process as well.
     *
     * @param array<non-empty-string, mixed>                    $arguments
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     *
     * @return Closure(): iterable<string>
     */
    public function describerOfGeneratedExample(array $arguments, ChoiceSource $source, DrawnValues $drawnValues, array $generators): Closure
    {
        return function () use ($arguments, $source, $drawnValues, $generators): iterable
        {
            $exporter = $this->exporter($source->constructions());
            $values   = [];

            foreach ($drawnValues->exportedWith($exporter) as $drawnValue) {
                $values[] = new Event\Code\DrawnValue($drawnValue['label'], $drawnValue['value']);
            }

            return PropertyFalsifiedToStringMapper::mapExampleThatEndedTheProcess(
                new Event\Code\Counterexample(
                    $source->sequence()->choices(),
                    $this->export($arguments, $source->constructions()),
                    null,
                    $values,
                    $drawnValues->stepsExportedWith($exporter),
                ),
                fn (): ?string => $this->formatIgnoringIssues($this->argumentsGeneratedFrom($generators, $source->sequence(), $source->deriver())),
            );
        };
    }

    /**
     * Describes an explicit example for the case that the PHP process ends
     * while it is run, see describerOfGeneratedExample().
     *
     * @param array<non-empty-string, string> $exportedArguments the arguments of the example, exported as PHP code
     * @param array<non-empty-string, mixed>  $arguments
     *
     * @return Closure(): iterable<string>
     */
    public function describerOfExplicitExample(array $exportedArguments, array $arguments): Closure
    {
        return fn (): iterable => PropertyFalsifiedToStringMapper::mapExampleThatEndedTheProcess(
            new Event\Code\Counterexample([], $exportedArguments, null, [], []),
            fn (): ?string => $this->formatIgnoringIssues(static fn (): array => $arguments),
        );
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

    /**
     * @param Closure(): array<non-empty-string, mixed> $arguments returns the arguments that the formatter is called with, with the issues that are triggered meanwhile handled
     * @param Closure(Closure(): mixed): mixed          $call      calls the formatter with the issues that it triggers handled
     *
     * @return ?non-empty-string
     */
    private function callFormatter(Closure $arguments, Closure $call): ?string
    {
        $definition = $this->generatorResolver->definition();

        assert($definition !== null);

        $formatter = $definition->counterexampleFormatter();

        if ($formatter === null) {
            return null;
        }

        $name = $formatter['className'] . '::' . $formatter['methodName'] . '()';

        try {
            $arguments = $arguments();
        } catch (Throwable $t) {
            return sprintf('Counterexample formatter %s was not called because generating its arguments raised %s: %s', $name, $t::class, $t->getMessage());
        }

        $level = ob_get_level();

        ob_start();

        try {
            $formatted = $call(
                static fn (): mixed => new ReflectionMethod($formatter['className'], $formatter['methodName'])->invoke(null, ...array_values($arguments)),
            );
        } catch (Throwable $t) {
            return sprintf('Counterexample formatter %s raised %s: %s', $name, $t::class, $t->getMessage());
        } finally {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }

        if (!is_string($formatted)) {
            return sprintf('Counterexample formatter %s returned %s instead of a string', $name, get_debug_type($formatted));
        }

        if ($formatted === '') {
            return null;
        }

        return $formatted;
    }

    /**
     * @template T
     *
     * @param Closure(): T $callable
     *
     * @throws Throwable
     *
     * @return T
     */
    private static function ignoringIssues(Closure $callable): mixed
    {
        set_error_handler(static fn (): bool => true);

        try {
            return $callable();
        } finally {
            restore_error_handler();
        }
    }
}
