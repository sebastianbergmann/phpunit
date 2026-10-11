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

use const PHP_INT_MAX;
use function array_values;
use function assert;
use function intdiv;
use function min;
use function sprintf;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Framework\PropertyBased\ChoiceSequence;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ChoiceTree;
use PHPUnit\Framework\PropertyBased\Constructions;
use PHPUnit\Framework\PropertyBased\CounterexampleExporter;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\EventAggregator;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\ExplicitExample;
use PHPUnit\Framework\PropertyBased\Falsification;
use PHPUnit\Framework\PropertyBased\FlakyPropertyException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\GeneratorMethod;
use PHPUnit\Framework\PropertyBased\InvalidPropertyException;
use PHPUnit\Framework\PropertyBased\PropertyDefinition;
use PHPUnit\Framework\PropertyBased\PropertyDefinitionBuilder;
use PHPUnit\Framework\PropertyBased\Shrinker;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestRunner\ExampleRun;
use PHPUnit\Framework\TestRunner\RiskyTestChecks;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\ErrorHandler;
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
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use Throwable;

/**
 * Checks a property: runs its explicit examples and then its generated
 * examples, each as a new instance of the test case class, shrinks the first
 * counterexample that is generated, and replays the minimal counterexample.
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
    private int $numberOfExplicitExamplesChecked = 0;

    /**
     * How many of the generated examples that were checked were classified
     * with each label, in the order in which the labels were first used.
     *
     * @var list<array{label: non-empty-string, numberOfExamples: positive-int}>
     */
    private array $classifications = [];

    /**
     * Whether no further examples were checked because the test run was
     * aborted, for instance because its deadline has passed.
     */
    private bool $aborted = false;

    /**
     * Whether no further examples were generated because every example that
     * the generators can generate had been generated.
     */
    private bool $exhausted = false;

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
     * Whether PHPUnit's error handler handles the issues that are triggered
     * by the property, which it does unless #[WithoutErrorHandler] is used.
     */
    private bool $usesErrorHandler = true;

    /**
     * @param non-empty-string       $name
     * @param ?PropertyDefinition    $definition       null when the requirements of the property were not met while the test suite was built
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
        $valueObject = $test->valueObjectForEvents();

        $this->test = $valueObject;
        $this->seed = RandomNumberGeneratorSeeder::derive(ConfigurationRegistry::get()->randomSeed(), $valueObject->id());

        $this->usesErrorHandler = TestRunner::shouldErrorHandlerBeUsed($test);

        $events = new EventAggregator($emitter, Event\Facade::instance(), $valueObject);

        try {
            [$deriver, $generators] = $this->handlingIssues(
                function () use ($test, $emitter): array
                {
                    $this->triggerTestCaseContextIssues();

                    return $this->generators($test, $emitter);
                },
                $events,
            );
        } catch (Throwable $t) {
            $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

            return;
        }

        $skeleton = null;

        // the test run is aborted before an example is checked, but not before
        // the first one
        $attempted = false;

        // the explicit examples are checked first, in the order in which they
        // are specified; they are neither generated nor shrunk, and they do not
        // count towards the number of examples
        foreach ($this->explicitExamples() as $explicitExample) {
            if ($attempted && $this->shouldStop()) {
                $this->aborted = true;

                break;
            }

            $attempted = true;

            // the arguments are exported before the example is run, which can
            // change the state of an object that is passed to it
            $exportedArguments     = $this->export($explicitExample->arguments(), new Constructions);
            $example               = $this->example($explicitExample->arguments(), true);
            [$exampleEvents, $run] = $this->runExample($example, $emitter, true);
            $falsification         = Falsification::of($example);

            if ($this->wasRejected($example, $falsification)) {
                $events->record($exampleEvents, false);

                $this->recordPhpunitWarning(
                    $events,
                    $emitter,
                    sprintf('Explicit example %s was rejected by assume()', $explicitExample->label()),
                );

                continue;
            }

            $this->numberOfExplicitExamplesChecked++;

            $this->recordCheckedExample($events, $exampleEvents, $run);

            if ($this->endsCheckWithoutFalsification($example, $falsification, $exampleEvents)) {
                $this->report($events, $emitter, $exampleEvents, null, false, null);

                return;
            }

            if ($falsification !== null) {
                $this->reportFalsificationByExplicitExample($events, $emitter, $example, $falsification, $exampleEvents, $explicitExample, $exportedArguments);

                return;
            }

            $skeleton = $exampleEvents;
        }

        $randomizer = new Randomizer(new Xoshiro256StarStar($this->seed));
        $tree       = new ChoiceTree;
        $remembered = $this->testRunHistory?->counterexample(TestRunHistoryId::fromReorderable($test));

        for ($i = 1; $i <= $this->maximumNumberOfExamplesGenerated(); $i++) {
            if ($this->numberOfExamplesChecked === $this->numberOfExamples) {
                break;
            }

            if ($tree->isExhausted()) {
                $this->exhausted = true;

                break;
            }

            if ($attempted && $this->shouldStop()) {
                $this->aborted = true;

                break;
            }

            $attempted = true;

            // the counterexample that was remembered when the property was last
            // falsified is checked before examples are generated; it is
            // replayed without drawing from the random number generator, so
            // the examples that are generated after it are the same as without
            // it
            if ($i === 1 && $remembered !== null) {
                $source = ChoiceSource::replay(new ChoiceSequence($remembered), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver, $tree);
            } else {
                $source = ChoiceSource::random($randomizer, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver, $tree);
            }

            try {
                $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), $events);
            } catch (ExampleRejectedException) {
                $this->numberOfExamplesRejected++;

                continue;
            } catch (Throwable $t) {
                $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

                return;
            }

            $example               = $this->example($arguments);
            [$exampleEvents, $run] = $this->runExample($example, $emitter, true);
            $falsification         = Falsification::of($example);

            if ($this->wasRejected($example, $falsification)) {
                $this->numberOfExamplesRejected++;

                $events->record($exampleEvents, false);

                continue;
            }

            $this->numberOfExamplesChecked++;

            $this->countClassifications($example->classificationsOfPropertyExample());
            $this->recordCheckedExample($events, $exampleEvents, $run);

            if ($this->endsCheckWithoutFalsification($example, $falsification, $exampleEvents)) {
                $this->report($events, $emitter, $exampleEvents, null, false, null);

                return;
            }

            if ($falsification !== null) {
                $throwable       = $example->throwableThatEndedTheTest();
                $originalFailure = null;

                if ($throwable !== null) {
                    $originalFailure = Event\Code\ThrowableBuilder::from($throwable);
                }

                // the example is released before its counterexample is shrunk,
                // and so is the throwable that ended it, whose stack trace can
                // hold the example as an argument
                unset($example, $throwable);

                $this->shrinkAndReport($events, $emitter, $deriver, $generators, $originalFailure, $falsification, $source->sequence());

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
    private function shrinkAndReport(EventAggregator $events, Event\Emitter $emitter, GeneratorDeriver $deriver, array $generators, ?Event\Code\Throwable $originalFailure, Falsification $falsification, ChoiceSequence $sequence): void
    {
        $numberOfExamplesChecked = $this->numberOfExamplesChecked;
        $maximumNumberOfAttempts = ConfigurationRegistry::get()->maximumShrinkingAttempts();

        if ($falsification->isTimeout()) {
            $maximumNumberOfAttempts = min($maximumNumberOfAttempts, self::MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS_AFTER_TIMEOUT);
        }

        $shrinker = new Shrinker(
            function (ChoiceSequence $candidate) use ($events, $emitter, $deriver, $generators, $falsification): ?ChoiceSequence
            {
                $source = ChoiceSource::replay($candidate, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

                try {
                    $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), null);
                } catch (Throwable) {
                    return null;
                }

                $example = $this->example($arguments);

                [$attemptEvents] = $this->runExample($example, $emitter, false);

                $events->recordShrinkingAttempt($attemptEvents);

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
            $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), $events);
        } catch (Throwable $t) {
            $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

            return;
        }

        $counterexample = $this->export($arguments, $source->constructions());
        $replay         = $this->example($arguments, true);
        [$replayEvents] = $this->runExample($replay, $emitter, false);

        // the issues of the final replay are reported like those of the
        // examples that were checked, the replay being the skeleton does not
        // report them
        $events->record($replayEvents, true);

        $error = null;

        // a final replay that is aborted because the deadline of the test run
        // has passed is reported with that error, like any other example that
        // is aborted by it, and with the counterexample, which falsified the
        // property while it was shrunk
        if (!$replay->throwableThatEndedTheTest() instanceof TimeLimitExceededException) {
            $replayFalsification = Falsification::of($replay);

            if ($replayFalsification === null) {
                $flaky = Event\Code\ThrowableBuilder::from(new FlakyPropertyException);

                // the failure that was originally observed is reported as the
                // cause of the error
                $error = new Event\Code\Throwable(
                    $flaky->className(),
                    $flaky->message(),
                    $flaky->description(),
                    $flaky->stackTrace(),
                    $originalFailure,
                );
            } elseif ($replayFalsification->isTimeout()) {
                $timeout = $replay->throwableThatEndedTheTest();

                assert($timeout !== null);

                $error = Event\Code\ThrowableBuilder::from($timeout);
            }
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
                    null,
                );
            },
        );
    }

    /**
     * Reports a property that was falsified by an explicit example with the
     * failure or error of that example, which is not shrunk, and with its
     * arguments.
     *
     * @param array<non-empty-string, string> $exportedArguments the arguments of the example, exported as PHP code
     */
    private function reportFalsificationByExplicitExample(EventAggregator $events, Event\Emitter $emitter, TestCase $example, Falsification $falsification, EventCollection $exampleEvents, ExplicitExample $explicitExample, array $exportedArguments): void
    {
        $error = null;

        // an example that exceeded its time limit is reported with that error
        // rather than as risky, because it falsified the property
        if ($falsification->isTimeout()) {
            $timeout = $example->throwableThatEndedTheTest();

            assert($timeout !== null);

            $error = Event\Code\ThrowableBuilder::from($timeout);
        }

        $test = $this->test;
        $seed = $this->seed;
        $name = $explicitExample->name();

        assert($test !== null);

        $this->report(
            $events,
            $emitter,
            $exampleEvents,
            $error,
            false,
            static function () use ($emitter, $test, $seed, $exportedArguments, $name): void
            {
                $emitter->testPropertyFalsified($test, $seed, 0, 0, 0, false, false, [], $exportedArguments, $name);
            },
        );
    }

    /**
     * @param list<non-empty-string> $labels the labels with which an example was classified
     */
    private function countClassifications(array $labels): void
    {
        foreach ($labels as $label) {
            foreach ($this->classifications as $index => $classification) {
                if ($classification['label'] === $label) {
                    $this->classifications[$index]['numberOfExamples']++;

                    continue 2;
                }
            }

            $this->classifications[] = ['label' => $label, 'numberOfExamples' => 1];
        }
    }

    /**
     * Whether an example was discarded because assume() was called with
     * false: an example that falsified the property or was aborted because
     * the deadline of the test run has passed was not.
     */
    private function wasRejected(TestCase $example, ?Falsification $falsification): bool
    {
        return $falsification === null &&
               !$example->throwableThatEndedTheTest() instanceof TimeLimitExceededException &&
               $example->wasRejectedByAssumption();
    }

    private function recordCheckedExample(EventAggregator $events, EventCollection $exampleEvents, ExampleRun $run): void
    {
        $this->numberOfAssertions += $run->numberOfAssertions();

        if ($run->doesNotPerformAssertions()) {
            $this->doesNotPerformAssertions = true;
        }

        if ($run->contributedToCodeCoverage() !== null && $this->contributedToCodeCoverage !== true) {
            $this->contributedToCodeCoverage = $run->contributedToCodeCoverage();
        }

        $events->record($exampleEvents, true);
    }

    /**
     * Whether the property is reported with the events of an example that was
     * checked, without the example falsifying it, so that no further examples
     * are checked:
     *
     * - an example that is aborted because the deadline of the test run has
     *   passed does not falsify the property
     * - an example that fails or errors before it is prepared, in setUp() or
     *   in another hook method that is called before the test method, does
     *   not falsify the property because the test method was not called with
     *   its arguments, so it is reported without being shrunk
     * - when an example is skipped or marked incomplete, so is the property
     */
    private function endsCheckWithoutFalsification(TestCase $example, ?Falsification $falsification, EventCollection $exampleEvents): bool
    {
        if ($example->throwableThatEndedTheTest() instanceof TimeLimitExceededException) {
            return true;
        }

        if ($falsification !== null) {
            return $this->endedBeforeItWasPrepared($exampleEvents);
        }

        return $example->status()->isSkipped() || $example->status()->isIncomplete();
    }

    /**
     * Records a PHPUnit warning for the property, which is reported with the
     * issues of its examples.
     *
     * @param non-empty-string $message
     */
    private function recordPhpunitWarning(EventAggregator $events, Event\Emitter $emitter, string $message): void
    {
        $test   = $this->test;
        $facade = Event\Facade::instance();

        assert($test !== null);

        $facade->startCollectingEvents();

        try {
            $emitter->testTriggeredPhpunitWarning($test, $message);
        } finally {
            $events->record($facade->stopCollectingEvents(), true);
        }
    }

    /**
     * @param bool             $passed        whether the property passed, that is, whether all examples that were checked passed
     * @param ?Closure(): void $beforeOutcome
     */
    private function report(EventAggregator $events, Event\Emitter $emitter, ?EventCollection $skeleton, ?Event\Code\Throwable $error, bool $passed, ?Closure $beforeOutcome): void
    {
        $test = $this->test;

        assert($test !== null);

        $this->decideHeldBackCodeCoverage($events, $test);

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
                    $this->classifications,
                );
            },
            $this->numberOfAssertions,
        );
    }

    /**
     * The code coverage data of the examples that passed without performing
     * assertions was held back (see TestRunner): it is appended when the
     * property as a whole performed assertions, and discarded otherwise, like
     * the data of a test that is considered risky because it did not perform
     * any assertions.
     */
    private function decideHeldBackCodeCoverage(EventAggregator $events, Event\Code\TestMethod $test): void
    {
        $codeCoverage = CodeCoverage::instance();

        if (!$codeCoverage->isActive()) {
            return;
        }

        if ($this->numberOfAssertions === 0) {
            $codeCoverage->discardHeldBack($test->id());

            return;
        }

        $facade = Event\Facade::instance();

        $facade->startCollectingEvents();

        try {
            $contributed = $codeCoverage->appendHeldBack($test->id());
        } finally {
            $events->record($facade->stopCollectingEvents(), true);
        }

        if ($contributed !== null && $this->contributedToCodeCoverage !== true) {
            $this->contributedToCodeCoverage = $contributed;
        }
    }

    /**
     * @return list<non-empty-string>
     */
    private function riskyMessages(bool $passed): array
    {
        $messages = [];

        // a property for which fewer examples could be generated than were
        // to be checked is only considered risky when none of them was
        // checked because all of them were rejected
        if ($this->numberOfExamplesChecked < $this->numberOfExamples &&
            ($this->numberOfExamplesChecked + $this->numberOfExamplesRejected === $this->maximumNumberOfExamplesGenerated() ||
             ($this->exhausted && $this->numberOfExamplesChecked === 0))) {
            $messages[] = sprintf(
                'This property was checked for only %d of %d examples, %d examples were rejected',
                $this->numberOfExamplesChecked,
                $this->numberOfExamples,
                $this->numberOfExamplesRejected,
            );
        }

        if ($this->aborted) {
            $messages[] = sprintf(
                'This property was checked for only %d of %d examples because the test run was aborted',
                $this->numberOfExamplesChecked,
                $this->numberOfExamples,
            );
        }

        if ($passed && $this->numberOfExamplesChecked > 0) {
            foreach ($this->unmetClassificationRequirements() as $message) {
                $messages[] = $message;
            }
        }

        // the reasons that concern the property as a whole are decided like
        // those of a test, on all examples that were checked
        $checks  = new RiskyTestChecks(ConfigurationRegistry::get()->reportUselessTests());
        $reasons = [];

        if ($passed) {
            $reasons[] = $checks->didNotContributeToCodeCoverage($this->contributedToCodeCoverage);
        }

        if ($passed && $this->numberOfExamplesChecked + $this->numberOfExplicitExamplesChecked > 0) {
            $reasons[] = $checks->performedNoAssertions($this->numberOfAssertions, $this->doesNotPerformAssertions);
        }

        $reasons[] = $checks->performedUnexpectedAssertions($this->numberOfAssertions, $this->doesNotPerformAssertions);

        foreach ($reasons as $reason) {
            if ($reason !== null) {
                $messages[] = $reason;
            }
        }

        return $messages;
    }

    /**
     * The messages for the labels with which fewer of the generated examples
     * that were checked were classified than #[RequireClassification]
     * requires.
     *
     * @return list<non-empty-string>
     */
    private function unmetClassificationRequirements(): array
    {
        if ($this->definition === null) {
            return [];
        }

        $messages = [];

        foreach ($this->definition->requiredClassifications() as $requirement) {
            $numberOfExamples = 0;

            foreach ($this->classifications as $classification) {
                if ($classification['label'] === $requirement['label']) {
                    $numberOfExamples = $classification['numberOfExamples'];
                }
            }

            $percentage = 100 * $numberOfExamples / $this->numberOfExamplesChecked;

            if ($percentage < $requirement['minimumPercentage']) {
                $messages[] = sprintf(
                    'Only %.1f%% of the examples of this property were classified as "%s", but at least %.1f%% are required',
                    $percentage,
                    $requirement['label'],
                    $requirement['minimumPercentage'],
                );
            }
        }

        return $messages;
    }

    /**
     * @return positive-int
     */
    private function maximumNumberOfExamplesGenerated(): int
    {
        // the number of examples is not limited, so the product could exceed
        // the largest integer
        if ($this->numberOfExamples > intdiv(PHP_INT_MAX, self::MAXIMUM_NUMBER_OF_EXAMPLES_GENERATED_PER_EXAMPLE)) {
            return PHP_INT_MAX;
        }

        return $this->numberOfExamples * self::MAXIMUM_NUMBER_OF_EXAMPLES_GENERATED_PER_EXAMPLE;
    }

    /**
     * @throws InvalidPropertyException
     *
     * @return array{0: GeneratorDeriver, 1: array<non-empty-string, AbstractGenerator<mixed>>}
     */
    private function generators(TestCase $test, Event\Emitter $emitter): array
    {
        // the definition of a property whose requirements were not met while
        // the test suite was built is built when the property runs and its
        // requirements are met then, because the function that it requires
        // is declared by setUpBeforeClass(), for instance
        if ($this->definition === null &&
            new Requirements($emitter)->requirementsNotSatisfiedFor($test::class, $test->name()) === []) {
            $this->definition = new PropertyDefinitionBuilder()->build($test::class, $test->name());
        }

        // the examples of a property whose requirements are not met are
        // skipped before arguments are passed to them
        if ($this->definition === null) {
            return [new GeneratorDeriver(GeneratorRegistry::empty()), []];
        }

        $registered = [];

        foreach ($this->definition->registeredGenerators() as $registeredClassName => $generatorMethod) {
            $registered[$registeredClassName] = $this->callGeneratorMethod($generatorMethod);
        }

        // the generators that were derived from the types of the parameters
        // when the definition was built use the registered generators through
        // the deriver of the choice source
        $deriver    = new GeneratorDeriver(GeneratorRegistry::from($registered));
        $generators = [];

        foreach ($this->definition->parameters() as $parameterName => $generator) {
            if ($generator instanceof AbstractGenerator) {
                $generators[$parameterName] = $generator;

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
     * The explicit examples of a property whose requirements are not met are
     * not checked, because its first example is skipped.
     *
     * @return list<ExplicitExample>
     */
    private function explicitExamples(): array
    {
        if ($this->definition === null) {
            return [];
        }

        return $this->definition->explicitExamples();
    }

    /**
     * @throws InvalidPropertyException
     *
     * @return ValueGenerator<mixed>
     */
    private function callGeneratorMethod(GeneratorMethod $generatorMethod): ValueGenerator
    {
        return $this->ensureIsValueGenerator(
            new ReflectionMethod($generatorMethod->className(), $generatorMethod->methodName())->invoke(null),
            $generatorMethod->asString(),
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
    private function handlingIssues(Closure $callable, ?EventAggregator $events): mixed
    {
        $test   = $this->test;
        $facade = Event\Facade::instance();

        assert($test !== null);

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
    private function triggerTestCaseContextIssues(): void
    {
        if (!$this->usesErrorHandler) {
            return;
        }

        $test = $this->test;

        assert($test !== null);

        $throwable = ErrorHandler::instance()->triggerTestCaseContextIssuesOfProperty($test);

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
    private function generateArguments(array $generators, ChoiceSource $source): array
    {
        $arguments = [];

        try {
            foreach ($generators as $parameterName => $generator) {
                $arguments[$parameterName] = $generator->generate($source);
            }
        } finally {
            // the same choices generate the same arguments, or are rejected
            // again, so they are not drawn again
            $source->exampleGenerated();
        }

        return $arguments;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     * @param Constructions                  $constructions the objects that generators created with new while the arguments were generated
     *
     * @return array<non-empty-string, string>
     */
    private function export(array $arguments, Constructions $constructions): array
    {
        assert($this->definition !== null);

        $exporter = new CounterexampleExporter(
            NameScope::forMethod(new ReflectionMethod($this->definition->className(), $this->definition->methodName())),
            $constructions,
        );

        $exported = [];

        foreach ($arguments as $parameterName => $argument) {
            $exported[$parameterName] = $exporter->export($argument);
        }

        return $exported;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     * @param bool                           $failureIsReported whether the failure or error of the example is the one that is reported for the property, which it is for the final replay of the minimal counterexample and for an explicit example
     */
    private function example(array $arguments, bool $failureIsReported = false): TestCase
    {
        assert($this->exampleFactory !== null);

        $example = ($this->exampleFactory)();

        $example->setPropertyExample(array_values($arguments), $failureIsReported);

        return $example;
    }

    /**
     * Runs an example with its events collected instead of dispatched, with
     * the test runner rather than TestCase::run(), which would resolve
     * dependencies that a property cannot have.
     *
     * @throws Throwable
     *
     * @return array{0: EventCollection, 1: ExampleRun}
     */
    private function runExample(TestCase $example, Event\Emitter $emitter, bool $collectCodeCoverage): array
    {
        $run = null;

        $events = $this->collectingEvents(
            static function () use ($example, $emitter, $collectCodeCoverage, &$run): void
            {
                $run = new TestRunner($emitter)->runExampleOfProperty($example, $collectCodeCoverage);
            },
        );

        assert($run instanceof ExampleRun);

        return [$events, $run];
    }

    /**
     * Whether an example ended before it was prepared, which its events tell
     * because the Test\Prepared event is emitted once the hook methods that
     * are called before the test method have been called.
     */
    private function endedBeforeItWasPrepared(EventCollection $exampleEvents): bool
    {
        foreach ($exampleEvents as $event) {
            if ($event instanceof Prepared) {
                return false;
            }
        }

        return true;
    }

    private function shouldStop(): bool
    {
        return TimeLimitHandler::deadlineReached() || TestResultFacade::shouldStop();
    }
}
