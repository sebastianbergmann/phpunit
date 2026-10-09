<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function array_values;
use function assert;
use function min;
use function sprintf;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\PropertyBased\ChoiceSequence;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\CounterexampleExporter;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\EventAggregator;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\Falsification;
use PHPUnit\Framework\PropertyBased\FlakyPropertyException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\InvalidPropertyException;
use PHPUnit\Framework\PropertyBased\PropertyDefinition;
use PHPUnit\Framework\PropertyBased\Shrinker;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\Runner\RandomNumberGeneratorSeeder;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;
use PHPUnit\Runner\TimeLimit\TimeLimitExceededException;
use PHPUnit\Runner\TimeLimit\TimeLimitHandler;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use ReflectionMethod;
use ReflectionParameter;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use Throwable;

/**
 * Checks a property: runs its examples, each as a new instance of the test
 * case class with generated arguments, shrinks the first counterexample that
 * is found, and replays the minimal counterexample.
 *
 * The events of the examples, of the shrinking attempts, and of the final
 * replay are collected rather than dispatched, and the property is reported
 * as one test (see EventAggregator).
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PropertyTestSuite extends IterativeTestSuite
{
    /**
     * Examples are generated until the number of examples has been checked
     * or this many times the number of examples has been generated.
     */
    private const int MAXIMUM_NUMBER_OF_EXAMPLES_GENERATED_PER_EXAMPLE = 10;

    /**
     * Every shrinking attempt after an example exceeded its time limit can
     * take as long as the time limit, so that shrinking has to get by with
     * few attempts.
     */
    private const int MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS_AFTER_TIMEOUT = 10;
    private ?PropertyDefinition $definition                              = null;

    /**
     * @var positive-int
     */
    private int $numberOfExamples = 1;

    /**
     * @var ?Closure(): TestCase
     */
    private ?Closure $exampleFactory        = null;
    private ?TestCase $unusedExample        = null;
    private ?Event\Code\TestMethod $test    = null;
    private ?TestRunHistory $testRunHistory = null;

    /**
     * @var non-negative-int
     */
    private int $seed = 0;

    /**
     * @var non-negative-int
     */
    private int $numberOfExamplesChecked = 0;

    /**
     * @var non-negative-int
     */
    private int $numberOfExamplesRejected = 0;

    /**
     * @var non-negative-int
     */
    private int $numberOfAssertions        = 0;
    private bool $doesNotPerformAssertions = false;

    /**
     * Whether one of the examples that were checked contributed to code
     * coverage, null when this was not checked for any of them.
     */
    private ?bool $contributedToCodeCoverage = null;

    /**
     * @param non-empty-string       $name
     * @param ?PropertyDefinition    $definition       null when the property is not checked because its requirements are not met
     * @param positive-int           $numberOfExamples
     * @param Closure(): TestCase    $exampleFactory
     * @param list<non-empty-string> $groups
     */
    public static function fromTestCase(string $name, Event\Emitter $emitter, TestCase $test, ?PropertyDefinition $definition, int $numberOfExamples, Closure $exampleFactory, array $groups = []): self
    {
        $suite = self::empty($name, $emitter);

        $suite->definition       = $definition;
        $suite->numberOfExamples = $numberOfExamples;
        $suite->exampleFactory   = $exampleFactory;

        $suite->addTest($test, $groups);

        return $suite;
    }

    /**
     * The number of examples for which the property is to be checked.
     *
     * @return positive-int
     */
    public function numberOfExamples(): int
    {
        return $this->numberOfExamples;
    }

    /**
     * The test run history from which the counterexample that was remembered
     * when the property was last falsified is taken.
     */
    public function setTestRunHistory(TestRunHistory $testRunHistory): void
    {
        $this->testRunHistory = $testRunHistory;
    }

    /**
     * @param list<Test> $tests
     *
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    protected function execute(array $tests, Event\Emitter $emitter): void
    {
        if ($tests !== []) {
            assert($tests[0] instanceof TestCase);

            $this->check($tests[0], $emitter);
        }
    }

    /**
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    private function check(TestCase $test, Event\Emitter $emitter): void
    {
        if (TestResultFacade::shouldStop()) {
            $emitter->testRunnerExecutionAborted();

            return;
        }

        $valueObject = $test->valueObjectForEvents();

        $this->test          = $valueObject;
        $this->seed          = RandomNumberGeneratorSeeder::derive(ConfigurationRegistry::get()->randomSeed(), $valueObject->id());
        $this->unusedExample = $test;

        $events = new EventAggregator($emitter, Event\Facade::instance(), $valueObject);

        try {
            [$deriver, $generators] = $this->generators();
        } catch (Throwable $t) {
            $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

            return;
        }

        $randomizer = new Randomizer(new Xoshiro256StarStar($this->seed));
        $skeleton   = null;
        $remembered = $this->testRunHistory?->counterexample(TestRunHistoryId::fromReorderable($test));

        for ($i = 1; $i <= $this->maximumNumberOfExamplesGenerated(); $i++) {
            if ($this->numberOfExamplesChecked === $this->numberOfExamples) {
                break;
            }

            if ($i > 1 && $this->shouldStop()) {
                break;
            }

            // the counterexample that was remembered when the property was last
            // falsified is checked first; it is replayed without drawing from
            // the random number generator, so the examples that are generated
            // after it are the same as without it
            if ($i === 1 && $remembered !== null) {
                $source = ChoiceSource::replay(new ChoiceSequence($remembered), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);
            } else {
                $source = ChoiceSource::random($randomizer, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);
            }

            try {
                $arguments = $this->generateArguments($generators, $source);
            } catch (ExampleRejectedException) {
                $this->numberOfExamplesRejected++;

                continue;
            } catch (Throwable $t) {
                $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

                return;
            }

            $example        = $this->example($arguments);
            $exampleEvents  = $this->runExample($example, $emitter, true);
            $falsification  = Falsification::of($example);
            $abortedByLimit = $example->throwableThatEndedTheTest() instanceof TimeLimitExceededException;

            if ($falsification === null && !$abortedByLimit && $example->wasRejectedByAssumption()) {
                $this->numberOfExamplesRejected++;

                $events->record($exampleEvents, false);

                continue;
            }

            $this->numberOfExamplesChecked++;
            $this->numberOfAssertions += $example->numberOfAssertionsPerformed();

            if ($example->doesNotPerformAssertions()) {
                $this->doesNotPerformAssertions = true;
            }

            if ($example->contributedToCodeCoverage() !== null && $this->contributedToCodeCoverage !== true) {
                $this->contributedToCodeCoverage = $example->contributedToCodeCoverage();
            }

            $events->record($exampleEvents, true);

            // an example that is aborted because the deadline of the test run
            // has passed does not falsify the property
            if ($abortedByLimit) {
                $this->report($events, $emitter, $exampleEvents, null, false, null);

                return;
            }

            if ($falsification !== null) {
                $this->shrinkAndReport($events, $emitter, $deriver, $generators, $example, $falsification, $source->sequence());

                return;
            }

            if ($example->status()->isSkipped() || $example->status()->isIncomplete()) {
                $this->report($events, $emitter, $exampleEvents, null, false, null);

                return;
            }

            $skeleton = $exampleEvents;
        }

        $this->report($events, $emitter, $skeleton, null, true, null);

        PassedTests::instance()->testMethodPassed($valueObject, null);
    }

    /**
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     *
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     */
    private function shrinkAndReport(EventAggregator $events, Event\Emitter $emitter, GeneratorDeriver $deriver, array $generators, TestCase $falsifyingExample, Falsification $falsification, ChoiceSequence $sequence): void
    {
        $numberOfExamplesChecked = $this->numberOfExamplesChecked;
        $maximumNumberOfAttempts = ConfigurationRegistry::get()->maximumShrinkingAttempts();

        if ($falsification->isTimeout()) {
            $maximumNumberOfAttempts = min($maximumNumberOfAttempts, self::MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS_AFTER_TIMEOUT);
        }

        $shrinker = new Shrinker(
            function (ChoiceSequence $candidate) use ($emitter, $deriver, $generators, $falsification): ?ChoiceSequence
            {
                $source = ChoiceSource::replay($candidate, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

                try {
                    $arguments = $this->generateArguments($generators, $source);
                } catch (Throwable) {
                    return null;
                }

                $example = $this->example($arguments);

                $this->runExample($example, $emitter, false);

                $candidateFalsification = Falsification::of($example);

                if ($candidateFalsification === null || !$candidateFalsification->isSameAs($falsification)) {
                    return null;
                }

                return $source->sequence();
            },
            $maximumNumberOfAttempts,
            $this->shouldStop(...),
        );

        $result = $shrinker->shrink($sequence);
        $source = ChoiceSource::replay($result->sequence(), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

        try {
            $arguments = $this->generateArguments($generators, $source);
        } catch (Throwable $t) {
            $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

            return;
        }

        $counterexample = $this->export($arguments, $source);
        $replay         = $this->example($arguments);
        $replayEvents   = $this->runExample($replay, $emitter, false);

        // a final replay that is aborted because the deadline of the test run
        // has passed is reported like any other example that is aborted by it
        if ($replay->throwableThatEndedTheTest() instanceof TimeLimitExceededException) {
            $this->report($events, $emitter, $replayEvents, null, false, null);

            return;
        }

        $replayFalsification = Falsification::of($replay);
        $error               = null;

        if ($replayFalsification === null) {
            $error = Event\Code\ThrowableBuilder::from(
                FlakyPropertyException::originallyFalsifiedBy($falsifyingExample->throwableThatEndedTheTest()),
            );
        } elseif ($replayFalsification->isTimeout()) {
            $timeout = $replay->throwableThatEndedTheTest();

            assert($timeout !== null);

            $error = Event\Code\ThrowableBuilder::from($timeout);
        }

        $test = $this->test;
        $seed = $this->seed;

        assert($test !== null);

        $this->report(
            $events,
            $emitter,
            $replayEvents,
            $error,
            false,
            static function () use ($emitter, $test, $seed, $numberOfExamplesChecked, $result, $counterexample): void
            {
                assert($numberOfExamplesChecked > 0);

                $emitter->testPropertyFalsified(
                    $test,
                    $seed,
                    $numberOfExamplesChecked,
                    $result->numberOfAttempts(),
                    $result->numberOfSteps(),
                    $result->budgetExhausted(),
                    $result->interrupted(),
                    $result->sequence()->choices(),
                    $counterexample,
                );
            },
        );
    }

    /**
     * @param bool             $passed        whether the property passed, that is, whether all examples that were checked passed
     * @param ?Closure(): void $beforeOutcome
     */
    private function report(EventAggregator $events, Event\Emitter $emitter, ?EventCollection $skeleton, ?Event\Code\Throwable $error, bool $passed, ?Closure $beforeOutcome): void
    {
        $test = $this->test;

        assert($test !== null);

        $events->dispatch(
            $skeleton,
            $error,
            $passed,
            $beforeOutcome,
            function () use ($emitter, $test, $passed): void
            {
                foreach ($this->riskyMessages($passed) as $message) {
                    $emitter->testConsideredRisky($test, $message);
                }

                $emitter->testPropertyChecked(
                    $test,
                    $this->seed,
                    $this->numberOfExamplesChecked,
                    $this->numberOfExamplesRejected,
                );
            },
            $this->numberOfAssertions,
        );
    }

    /**
     * @return list<non-empty-string>
     */
    private function riskyMessages(bool $passed): array
    {
        $messages = [];

        if ($this->numberOfExamplesChecked < $this->numberOfExamples &&
            $this->numberOfExamplesChecked + $this->numberOfExamplesRejected === $this->maximumNumberOfExamplesGenerated()) {
            $messages[] = sprintf(
                'This property was checked for only %d of %d examples, %d examples were rejected',
                $this->numberOfExamplesChecked,
                $this->numberOfExamples,
                $this->numberOfExamplesRejected,
            );
        }

        if ($passed && $this->contributedToCodeCoverage === false) {
            $messages[] = 'This test does not contribute to code coverage';
        }

        if ($passed &&
            $this->numberOfExamplesChecked > 0 &&
            ConfigurationRegistry::get()->reportUselessTests() &&
            !$this->doesNotPerformAssertions &&
            $this->numberOfAssertions === 0) {
            $messages[] = 'This test did not perform any assertions';
        }

        if ($this->doesNotPerformAssertions && $this->numberOfAssertions > 0) {
            $plural = '';

            if ($this->numberOfAssertions > 1) {
                $plural = 's';
            }

            $messages[] = sprintf(
                'This test is not expected to perform assertions but performed %d assertion%s',
                $this->numberOfAssertions,
                $plural,
            );
        }

        return $messages;
    }

    /**
     * @return positive-int
     */
    private function maximumNumberOfExamplesGenerated(): int
    {
        return $this->numberOfExamples * self::MAXIMUM_NUMBER_OF_EXAMPLES_GENERATED_PER_EXAMPLE;
    }

    /**
     * @throws InvalidPropertyException
     *
     * @return array{0: GeneratorDeriver, 1: array<non-empty-string, AbstractGenerator<mixed>>}
     */
    private function generators(): array
    {
        if ($this->definition === null) {
            return [new GeneratorDeriver(GeneratorRegistry::empty()), []];
        }

        $className  = $this->definition->className();
        $methodName = $this->definition->methodName();
        $registered = [];

        foreach ($this->definition->registeredGenerators() as $registeredClassName => $generatorMethodName) {
            $registered[$registeredClassName] = $this->callGeneratorMethod($generatorMethodName);
        }

        $deriver    = new GeneratorDeriver(GeneratorRegistry::from($registered));
        $generators = [];

        foreach ($this->definition->parameters() as $parameterName => $generator) {
            if ($generator === null) {
                $generators[$parameterName] = $deriver->forParameter(
                    new ReflectionParameter([$className, $methodName], $parameterName),
                );

                continue;
            }

            if ($generator instanceof Closure) {
                $generators[$parameterName] = AbstractGenerator::from(
                    $this->ensureIsValueGenerator(
                        $generator(),
                        sprintf('the closure that is specified with #[ForAll] for parameter $%s', $parameterName),
                    ),
                );

                continue;
            }

            $generators[$parameterName] = AbstractGenerator::from($this->callGeneratorMethod($generator));
        }

        return [$deriver, $generators];
    }

    /**
     * @param non-empty-string $methodName
     *
     * @throws InvalidPropertyException
     *
     * @return ValueGenerator<mixed>
     */
    private function callGeneratorMethod(string $methodName): ValueGenerator
    {
        assert($this->definition !== null);

        $className = $this->definition->className();

        return $this->ensureIsValueGenerator(
            new ReflectionMethod($className, $methodName)->invoke(null),
            sprintf('%s::%s()', $className, $methodName),
        );
    }

    /**
     * @param non-empty-string $source what returned $generator, for the message of the exception
     *
     * @throws InvalidPropertyException
     *
     * @return ValueGenerator<mixed>
     */
    private function ensureIsValueGenerator(mixed $generator, string $source): ValueGenerator
    {
        assert($this->definition !== null);

        if (!$generator instanceof ValueGenerator) {
            throw InvalidPropertyException::because(
                $this->definition->className(),
                $this->definition->methodName(),
                sprintf(
                    '%s does not return a %s',
                    $source,
                    ValueGenerator::class,
                ),
            );
        }

        return $generator;
    }

    /**
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     *
     * @throws ExampleRejectedException
     *
     * @return array<non-empty-string, mixed>
     */
    private function generateArguments(array $generators, ChoiceSource $source): array
    {
        $arguments = [];

        foreach ($generators as $parameterName => $generator) {
            $arguments[$parameterName] = $generator->generate($source);
        }

        return $arguments;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     *
     * @return array<non-empty-string, string>
     */
    private function export(array $arguments, ChoiceSource $source): array
    {
        assert($this->definition !== null);

        $exporter = new CounterexampleExporter(
            NameScope::forMethod(new ReflectionMethod($this->definition->className(), $this->definition->methodName())),
            $source->constructions(),
        );

        $exported = [];

        foreach ($arguments as $parameterName => $argument) {
            $exported[$parameterName] = $exporter->export($argument);
        }

        return $exported;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     */
    private function example(array $arguments): TestCase
    {
        $example = $this->unusedExample;

        $this->unusedExample = null;

        if ($example === null) {
            assert($this->exampleFactory !== null);

            $example = ($this->exampleFactory)();
        }

        $example->setPropertyExample(array_values($arguments));

        return $example;
    }

    /**
     * Runs an example with its events collected instead of dispatched, the
     * same way that runCollectingEvents() runs a test, but with the test
     * runner rather than TestCase::run(), which would resolve dependencies
     * that a property cannot have.
     *
     * @throws Throwable
     */
    private function runExample(TestCase $example, Event\Emitter $emitter, bool $collectCodeCoverage): EventCollection
    {
        $facade = Event\Facade::instance();

        $facade->startCollectingEvents();

        try {
            new TestRunner($emitter)->run($example, $collectCodeCoverage);
        } catch (Throwable $t) {
            $facade->forward($facade->stopCollectingEvents());

            throw $t;
        }

        return $facade->stopCollectingEvents();
    }

    private function shouldStop(): bool
    {
        return TimeLimitHandler::deadlineReached() || TestResultFacade::shouldStop();
    }
}
