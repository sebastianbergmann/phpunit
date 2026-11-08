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

use function array_shift;
use function assert;
use function count;
use function hash;
use function hrtime;
use function intdiv;
use function max;
use function min;
use function sprintf;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Constructions;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\DrawnValues;
use PHPUnit\Framework\PropertyBased\EventAggregator;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\ExampleResult;
use PHPUnit\Framework\PropertyBased\ExampleRunner;
use PHPUnit\Framework\PropertyBased\ExplicitExample;
use PHPUnit\Framework\PropertyBased\Falsification;
use PHPUnit\Framework\PropertyBased\FalsificationReport;
use PHPUnit\Framework\PropertyBased\FlakyPropertyException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\GeneratorResolver;
use PHPUnit\Framework\PropertyBased\InvalidPropertyException;
use PHPUnit\Framework\PropertyBased\InvalidStateMachineException;
use PHPUnit\Framework\PropertyBased\PropertyDefinition;
use PHPUnit\Framework\PropertyBased\PropertyExample;
use PHPUnit\Framework\PropertyBased\PropertyStatistics;
use PHPUnit\Framework\TestRunner\ExampleRun;
use PHPUnit\Framework\TestRunner\TestRunner;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Runner\CodeCoverage;
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
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use SebastianBergmann\ChoiceSequence\ChoiceTree;
use SebastianBergmann\ChoiceSequence\Shrinker;
use SebastianBergmann\ChoiceSequence\TargetedSearch;
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
     * Every shrinking attempt after an example exceeded its time limit can
     * take as long as the time limit, so that shrinking has to get by with
     * few attempts.
     */
    private const int MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS_AFTER_TIMEOUT = 10;

    /**
     * How often the example that first falsified a property is replayed
     * before its counterexample is shrunk, to tell whether it falsifies the
     * property again.
     */
    private const int NUMBER_OF_REPLAYS_OF_FALSIFYING_EXAMPLE = 4;
    private ?GeneratorResolver $generatorResolver             = null;

    /**
     * @var positive-int
     */
    private int $numberOfExamples = 1;

    /**
     * @var ?Closure(): TestCase
     */
    private ?Closure $exampleFactory        = null;
    private ?Event\Code\TestMethod $test    = null;
    private ?ExampleRunner $exampleRunner   = null;
    private ?TestRunHistory $testRunHistory = null;

    /**
     * @var non-negative-int
     */
    private int $seed = 0;

    /**
     * The search for examples with higher scores than the ones that the
     * examples that were checked targeted, null before examples are
     * generated.
     */
    private ?TargetedSearch $targetedSearch = null;

    /**
     * The other ways in which the property was falsified while its
     * counterexample was shrunk, by their description, in the order in which
     * they were first seen, each with the simplest choice sequence that
     * falsified the property that way.
     *
     * @var array<non-empty-string, array{falsification: Falsification, sequence: ChoiceSequence}>
     */
    private array $otherFalsifications = [];

    /**
     * What the examples that were checked while the property was checked
     * amount to, null before it is checked.
     */
    private ?PropertyStatistics $statistics = null;

    /**
     * The deriver and the generators of the property, for running its
     * examples on their own, null before the first one is run.
     *
     * @var ?array{0: GeneratorDeriver, 1: array<non-empty-string, AbstractGenerator<mixed>>}
     */
    private ?array $generatorsOfExamplesRunOnTheirOwn = null;

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

        $suite->generatorResolver = new GeneratorResolver($definition);
        $suite->numberOfExamples  = $numberOfExamples;
        $suite->exampleFactory    = $exampleFactory;

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
     * The test run history from which the counterexamples that were
     * remembered when the property was last falsified are taken.
     */
    public function setTestRunHistory(TestRunHistory $testRunHistory): void
    {
        $this->testRunHistory = $testRunHistory;
    }

    /**
     * Runs one example of the property, with its full lifecycle, on its own
     * rather than while the property is checked, with arguments that are
     * generated from the given choices. The example is neither shrunk nor
     * reported, and the events that are emitted while it runs are not
     * dispatched.
     *
     * With $exportValues, the result has the arguments of the example, the
     * values that it drew with draw(), and the steps of its state machine,
     * exported as they are reported for a counterexample. They are not
     * exported otherwise, which saves the time that exporting them takes.
     *
     * @param list<non-negative-int> $choices
     * @param positive-int           $timeLimit the time limit of the example in seconds, which covers generating its arguments and running it
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidPropertyException
     * @throws UnintentionallyCoveredCodeException
     */
    public function replayExample(array $choices, int $timeLimit, bool $exportValues = false): ExampleResult
    {
        return $this->runExampleOnItsOwn(
            static fn (GeneratorDeriver $deriver): ChoiceSource => ChoiceSource::replay(new ChoiceSequence($choices), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver),
            $timeLimit,
            $exportValues,
        );
    }

    /**
     * Runs one example of the property, as replayExample() does, with
     * arguments that are generated from choices that are drawn from a random
     * number generator that is seeded with the given seed.
     *
     * @param positive-int $timeLimit the time limit of the example in seconds, which covers generating its arguments and running it
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidPropertyException
     * @throws UnintentionallyCoveredCodeException
     */
    public function generateExample(int $seed, int $timeLimit, bool $exportValues = false): ExampleResult
    {
        return $this->runExampleOnItsOwn(
            static fn (GeneratorDeriver $deriver): ChoiceSource => ChoiceSource::random(new Randomizer(new Xoshiro256StarStar($seed)), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver),
            $timeLimit,
            $exportValues,
        );
    }

    /**
     * Discards the generators of the examples that are run on their own, so
     * that they are created again for the next one. They are discarded when
     * the test class is finished, as they can depend on what the hook methods
     * that are called before its first test set up, setUpBeforeClass(), for
     * instance, which are called again when the test class is prepared again.
     */
    public function discardGeneratorsOfExamplesRunOnTheirOwn(): void
    {
        $this->generatorsOfExamplesRunOnTheirOwn = null;
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

        $this->startRunning($test, $valueObject);

        $configuration = ConfigurationRegistry::get();

        $this->seed       = RandomNumberGeneratorSeeder::derive($configuration->randomSeed(), $valueObject->id());
        $this->statistics = new PropertyStatistics(
            $this->numberOfExamples,
            $configuration->examplesCheckedUntilHealthIsAssumed(),
            $configuration->maximumRejectedExamplesUntilHealthIsAssumed(),
            $configuration->maximumTooLargeExamplesUntilHealthIsAssumed(),
            $configuration->reportUselessTests(),
        );

        $events = new EventAggregator($emitter, Event\Facade::instance(), $valueObject);

        try {
            [$deriver, $generators] = $this->exampleRunner()->handlingIssues(
                function () use ($test, $emitter): array
                {
                    $this->exampleRunner()->triggerTestCaseContextIssues();

                    return $this->generatorResolver()->generators($test, $emitter);
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
                $this->statistics()->recordThatTheTestRunWasAborted();

                break;
            }

            $attempted = true;

            // the arguments are exported before the example is run, which can
            // change the state of an object that is passed to it
            $exportedArguments     = $this->exampleRunner()->export($explicitExample->arguments(), new Constructions);
            $example               = $this->exampleRunner()->example($explicitExample->arguments(), true);
            [$exampleEvents, $run] = $this->exampleRunner()->run($example, $emitter, true);
            $falsification         = Falsification::of($example);

            if ($this->wasRejected($example, $falsification)) {
                $events->record($exampleEvents, false);

                $events->recordPhpunitWarning(
                    sprintf('Explicit example %s was rejected by assume()', $explicitExample->label()),
                );

                continue;
            }

            $this->recordCheckedExample($events, $exampleEvents, $run);

            if ($this->endsCheckWithoutFalsification($example, $falsification, $exampleEvents)) {
                $this->report($events, $emitter, $exampleEvents, $this->errorOfExampleThatEndsCheck($example, $falsification), false, null);

                return;
            }

            $this->statistics()->recordCheckedExplicitExample();

            if ($falsification !== null) {
                $this->reportFalsificationByExplicitExample($events, $emitter, $example, $falsification, $exampleEvents, $explicitExample, $exportedArguments);

                return;
            }

            $skeleton = $exampleEvents;
        }

        $randomizer = new Randomizer(new Xoshiro256StarStar($this->seed));
        $tree       = new ChoiceTree;
        $remembered = [];

        // the search draws from a random number generator of its own, so the
        // examples that are generated are the same with and without it
        $search = new TargetedSearch(new Randomizer(new Xoshiro256StarStar(hash('sha256', 'targeted search ' . $this->seed, true))));

        $this->targetedSearch = $search;

        if ($this->testRunHistory !== null) {
            $remembered = $this->testRunHistory->counterexamples(TestRunHistoryId::fromReorderable($test));
        }

        for ($i = 1; $i <= $this->statistics()->maximumNumberOfExamplesGenerated(); $i++) {
            if ($this->statistics()->allExamplesWereChecked()) {
                break;
            }

            if ($tree->isExhausted()) {
                $this->statistics()->recordThatEveryExampleWasGenerated();

                break;
            }

            if ($this->statistics()->tooManyExamplesWereRejected()) {
                break;
            }

            if ($attempted && $this->shouldStop()) {
                $this->statistics()->recordThatTheTestRunWasAborted();

                break;
            }

            $attempted = true;

            // the counterexamples that were remembered when the property was
            // last falsified, one for each way in which it was falsified, are
            // checked before examples are generated; they are replayed without
            // drawing from the random number generator, so the examples that
            // are generated after them are the same as without them
            if ($remembered !== []) {
                $source = ChoiceSource::replay(new ChoiceSequence(array_shift($remembered)), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver, $tree);
            } else {
                $source = $this->sourceOfGeneratedExample($search, $randomizer, $deriver, $tree);
            }

            try {
                $arguments = $this->exampleRunner()->handlingIssues(fn (): array => $this->exampleRunner()->generateArguments($generators, $source), $events);
            } catch (ExampleRejectedException $e) {
                // the same choices are rejected again, so they are not drawn
                // again
                $this->exampleGenerated($source, $events);

                $this->statistics()->recordRejectedExample($e->reason());
                $search->exampleRejected();

                continue;
            } catch (Throwable $t) {
                $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

                return;
            }

            $example               = $this->exampleRunner()->example($arguments, false, DrawnValues::from($source));
            [$exampleEvents, $run] = $this->exampleRunner()->run($example, $emitter, true);
            $falsification         = Falsification::of($example);

            // the same choices generate the same arguments and draw the same
            // values, or are rejected again, so they are not drawn again; the
            // example draws choices with draw() until it ends
            $this->exampleGenerated($source, $events);

            if ($this->wasRejected($example, $falsification)) {
                $this->statistics()->recordRejectedExample(self::propertyExampleOf($example)->rejectionReason());
                $search->exampleRejected();

                $events->record($exampleEvents, false);

                continue;
            }

            $this->recordCheckedExample($events, $exampleEvents, $run);

            if ($this->endsCheckWithoutFalsification($example, $falsification, $exampleEvents)) {
                $this->report($events, $emitter, $exampleEvents, $this->errorOfExampleThatEndsCheck($example, $falsification), false, null);

                return;
            }

            $this->statistics()->recordCheckedGeneratedExample(self::propertyExampleOf($example));

            $search->exampleChecked($source->sequence(), self::propertyExampleOf($example)->targets());

            if ($falsification !== null) {
                $throwable       = self::propertyExampleOf($example)->throwableThatEndedIt();
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
     * @param Closure(GeneratorDeriver): ChoiceSource $source       creates the source of the choices of the example
     * @param positive-int                            $timeLimit
     * @param bool                                    $exportValues whether the arguments, the drawn values, and the steps of the example are exported
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidPropertyException
     * @throws UnintentionallyCoveredCodeException
     */
    private function runExampleOnItsOwn(Closure $source, int $timeLimit, bool $exportValues): ExampleResult
    {
        $test    = $this->testOfProperty();
        $emitter = $this->emitter();

        if ($this->test === null) {
            $this->startRunning($test, $test->valueObjectForEvents());
        }

        [$deriver, $generators] = $this->generatorsOfExamplesRunOnTheirOwn();

        $source  = $source($deriver);
        $started = hrtime(true);

        try {
            $arguments = $this->exampleRunner()->handlingIssues(fn (): array => $this->exampleRunner()->generateArgumentsWithinTimeLimit($generators, $source, $timeLimit), null);
        } catch (ExampleRejectedException) {
            return ExampleResult::rejected($source->sequence());
        } catch (Throwable $t) {
            // a generator raised an exception, the constructor of a class
            // whose objects are generated, for instance, or the arguments took
            // longer to generate than the time limit allows, so that the
            // example did not check the property
            return ExampleResult::aborted(Falsification::ofThrowable($t), $source->sequence());
        }

        // the time limit covers generating the arguments and running the
        // example, which is left with the rest of the time limit, rounded up
        // to whole seconds as time limits are enforced in whole seconds
        $timeLimit = max(1, $timeLimit - intdiv(hrtime(true) - $started, 1_000_000_000));

        $exportedArguments = null;
        $drawnValues       = DrawnValues::from($source);

        // the arguments are exported before the example is run, which can
        // change the state of an object that is passed to it; the arguments
        // of a property whose requirements are not met are not exported,
        // because its example is skipped
        if ($exportValues && $this->generatorResolver()->definition() !== null) {
            $exportedArguments = $this->exampleRunner()->export($arguments, $source->constructions());
            $drawnValues       = DrawnValues::reportedFrom($source, $this->exampleRunner()->exporter($source->constructions()));
        }

        $example = $this->exampleRunner()->example($arguments, false, $drawnValues);

        [$exampleEvents] = $this->exampleRunner()->run($example, $emitter, false, $timeLimit);

        $result = $this->resultOf($example, $source, $exampleEvents);

        if ($exportedArguments === null) {
            return $result;
        }

        return $result->withExportedValues($exportedArguments, $drawnValues->exported(), $drawnValues->steps());
    }

    /**
     * The result of an example that was run on its own.
     */
    private function resultOf(TestCase $example, ChoiceSource $source, EventCollection $exampleEvents): ExampleResult
    {
        $falsification = Falsification::of($example);

        if ($this->wasRejected($example, $falsification)) {
            return ExampleResult::rejected($source->sequence());
        }

        if ($falsification !== null && $this->wasAborted($example, $exampleEvents)) {
            return ExampleResult::aborted($falsification, $source->sequence());
        }

        if ($falsification === null && $example->status()->isSkipped()) {
            return ExampleResult::skipped($source->sequence());
        }

        if ($falsification === null && $example->status()->isIncomplete()) {
            return ExampleResult::incomplete($source->sequence());
        }

        if ($falsification === null) {
            return ExampleResult::passed($source->sequence());
        }

        return ExampleResult::falsified($falsification, $example->status()->isFailure(), $source->sequence());
    }

    /**
     * The test of the property, which this test suite aggregates.
     */
    private function testOfProperty(): TestCase
    {
        $tests = $this->tests();

        assert(isset($tests[0]) && $tests[0] instanceof TestCase);

        return $tests[0];
    }

    /**
     * The generators are created once for the examples that are run on their
     * own. As these examples are run outside of a test run, there is no test
     * result for the property to which an exception that is raised while the
     * generators are created could be reported as its error, so it is passed
     * on as an exception of PHPUnit's own.
     *
     * @throws \PHPUnit\Exception
     *
     * @return array{0: GeneratorDeriver, 1: array<non-empty-string, AbstractGenerator<mixed>>}
     */
    private function generatorsOfExamplesRunOnTheirOwn(): array
    {
        if ($this->generatorsOfExamplesRunOnTheirOwn !== null) {
            return $this->generatorsOfExamplesRunOnTheirOwn;
        }

        $test        = $this->testOfProperty();
        $valueObject = $this->test;

        assert($valueObject !== null);

        try {
            $this->generatorsOfExamplesRunOnTheirOwn = $this->exampleRunner()->handlingIssues(
                function () use ($test): array
                {
                    $this->triggerTestCaseContextIssuesOfExamplesRunOnTheirOwn();

                    return $this->generatorResolver()->generators($test, $this->emitter());
                },
                null,
            );
        } catch (\PHPUnit\Exception $e) {
            throw $e;
        } catch (Throwable $t) {
            // a generator method, or a closure that is specified with
            // #[ForAll], raised an exception
            throw InvalidPropertyException::because(
                $valueObject->className(),
                $valueObject->methodName(),
                sprintf(
                    'creating its generators raised %s: %s',
                    Falsification::ofThrowable($t)->description(),
                    $t->getMessage(),
                ),
            );
        }

        return $this->generatorsOfExamplesRunOnTheirOwn;
    }

    /**
     * Triggers the issues that were triggered in the test case context of the
     * property, while the test suite was built, for instance, as when PHPUnit
     * checks the property, before the generators of the examples that are run
     * on their own are created.
     *
     * @throws InvalidPropertyException when an error handler that was registered before PHPUnit's own turned one of these issues into an exception
     */
    private function triggerTestCaseContextIssuesOfExamplesRunOnTheirOwn(): void
    {
        assert($this->test !== null);

        try {
            $this->exampleRunner()->triggerTestCaseContextIssues();
        } catch (Throwable $t) {
            throw InvalidPropertyException::because(
                $this->test->className(),
                $this->test->methodName(),
                sprintf(
                    'an error handler turned an issue that was triggered while the test suite was built into %s: %s',
                    Falsification::ofThrowable($t)->description(),
                    $t->getMessage(),
                ),
            );
        }
    }

    /**
     * Once half of the examples have been checked, the other half are spent
     * on searching for examples with higher scores, if the examples target
     * scores, until the search reaches local maxima; all other examples are
     * generated at random.
     */
    private function sourceOfGeneratedExample(TargetedSearch $search, Randomizer $randomizer, GeneratorDeriver $deriver, ChoiceTree $tree): ChoiceSource
    {
        if ($this->statistics()->halfOfTheExamplesWereChecked()) {
            $candidate = $search->nextCandidate();

            if ($candidate !== null) {
                return ChoiceSource::replay($candidate, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver, $tree);
            }
        }

        return ChoiceSource::random($randomizer, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver, $tree);
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
        $numberOfExamplesChecked = $this->statistics()->numberOfExamplesChecked();
        $replays                 = $this->replayFalsifyingExample($events, $emitter, $deriver, $generators, $falsification, $sequence);

        if ($replays === null) {
            return;
        }

        if ($replays['numberOfReplays'] > 0 && $replays['numberOfReproductions'] === 0) {
            $this->reportExampleThatDidNotFalsifyAgain($events, $emitter, $originalFailure, $sequence, $replays);

            return;
        }

        $numberOfReplays         = $replays['numberOfReplays'];
        $numberOfReproductions   = $replays['numberOfReproductions'];
        $maximumNumberOfAttempts = ConfigurationRegistry::get()->maximumShrinkingAttempts();

        if ($falsification->isTimeout()) {
            $maximumNumberOfAttempts = min($maximumNumberOfAttempts, self::MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS_AFTER_TIMEOUT);
        }

        $shrinker = new Shrinker(
            function (ChoiceSequence $candidate) use ($events, $emitter, $deriver, $generators, $falsification): ?ChoiceSequence
            {
                $source = ChoiceSource::replay($candidate, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

                try {
                    $arguments = $this->exampleRunner()->handlingIssues(fn (): array => $this->exampleRunner()->generateArguments($generators, $source), null);
                } catch (Throwable) {
                    return null;
                }

                $example = $this->exampleRunner()->example($arguments, false, DrawnValues::from($source));

                [$attemptEvents] = $this->exampleRunner()->run($example, $emitter, false);

                $events->recordShrinkingAttempt($attemptEvents);

                $candidateFalsification = Falsification::of($example);

                if ($candidateFalsification === null) {
                    return null;
                }

                if (!$candidateFalsification->isSameAs($falsification)) {
                    $this->rememberOtherFalsification($example, $candidateFalsification, $attemptEvents, $source->sequence());

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
            $arguments = $this->exampleRunner()->handlingIssues(fn (): array => $this->exampleRunner()->generateArguments($generators, $source), $events);
        } catch (Throwable $t) {
            $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

            return;
        }

        $counterexample = $this->exampleRunner()->export($arguments, $source->constructions());
        $drawnValues    = DrawnValues::reportedFrom($source, $this->exampleRunner()->exporter($source->constructions()));
        $replay         = $this->exampleRunner()->example($arguments, true, $drawnValues);
        [$replayEvents] = $this->exampleRunner()->run($replay, $emitter, false);

        // the issues of the final replay are reported like those of the
        // examples that were checked, the replay being the skeleton does not
        // report them
        $events->record($replayEvents, true);

        $error = null;

        // a final replay that is aborted because the deadline of the test run
        // has passed is reported with that error, like any other example that
        // is aborted by it, and with the counterexample, which falsified the
        // property while it was shrunk
        if (!self::propertyExampleOf($replay)->throwableThatEndedIt() instanceof TimeLimitExceededException) {
            $replayFalsification = Falsification::of($replay);

            if ($replayFalsification === null) {
                $flaky = Event\Code\ThrowableBuilder::from(FlakyPropertyException::minimalCounterexampleNotFalsifyingAgain());

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
                $timeout = self::propertyExampleOf($replay)->throwableThatEndedIt();

                assert($timeout !== null);

                $error = Event\Code\ThrowableBuilder::from($timeout);
            }
        }

        $test                = $this->test;
        $seed                = $this->seed;
        $exportedDrawnValues = $drawnValues->exported();
        $steps               = $drawnValues->steps();
        $otherFalsifications = $this->replayOtherFalsifications($events, $emitter, $deriver, $generators);

        assert($test !== null);

        $this->report(
            $events,
            $emitter,
            $replayEvents,
            $error,
            false,
            new FalsificationReport(
                $seed,
                $numberOfExamplesChecked,
                $result->numberOfAttempts(),
                $result->numberOfSteps(),
                $result->budgetExhausted(),
                $result->interrupted(),
                self::counterexample($result->sequence()->choices(), $counterexample, null, $exportedDrawnValues, $steps),
                $otherFalsifications,
                $numberOfReplays,
                $numberOfReproductions,
            ),
        );
    }

    /**
     * Replays the example that first falsified the property before its
     * counterexample is shrunk, and counts how often it falsifies the
     * property in the same way again. A failure that depends on something
     * other than the arguments, timing or shared state, for instance, is told
     * this way before the budget for shrinking is spent on it.
     *
     * An example that exceeded its time limit is not replayed, because every
     * replay would take as long as the time limit. A replay that is aborted
     * because the deadline of the test run has passed ends the replays and is
     * not counted, and neither is a replay whose generators did not generate
     * the same arguments and values from the same choices.
     *
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     *
     * @return ?array{numberOfReplays: non-negative-int, numberOfReproductions: non-negative-int, counterexample: array<non-empty-string, string>, drawnValues: list<array{label: ?non-empty-string, value: string}>, steps: list<non-empty-string>, events: ?EventCollection} the replays, with the arguments and values of the last replay that was counted, exported, and its events, or null when the arguments could not be generated again, which was reported
     */
    private function replayFalsifyingExample(EventAggregator $events, Event\Emitter $emitter, GeneratorDeriver $deriver, array $generators, Falsification $falsification, ChoiceSequence $sequence): ?array
    {
        $replays = [
            'numberOfReplays'       => 0,
            'numberOfReproductions' => 0,
            'counterexample'        => [],
            'drawnValues'           => [],
            'steps'                 => [],
            'events'                => null,
        ];

        if ($falsification->isTimeout()) {
            return $replays;
        }

        for ($replay = 1; $replay <= self::NUMBER_OF_REPLAYS_OF_FALSIFYING_EXAMPLE && !$this->shouldStop(); $replay++) {
            $source = ChoiceSource::replay($sequence, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

            try {
                $arguments = $this->exampleRunner()->handlingIssues(fn (): array => $this->exampleRunner()->generateArguments($generators, $source), null);
            } catch (Throwable $t) {
                $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

                return null;
            }

            $counterexample = $this->exampleRunner()->export($arguments, $source->constructions());
            $drawnValues    = DrawnValues::reportedFrom($source, $this->exampleRunner()->exporter($source->constructions()));
            $example        = $this->exampleRunner()->example($arguments, false, $drawnValues);

            [$replayEvents] = $this->exampleRunner()->run($example, $emitter, false);

            $events->recordShrinkingAttempt($replayEvents);

            if (self::propertyExampleOf($example)->throwableThatEndedIt() instanceof TimeLimitExceededException) {
                break;
            }

            if ($source->sequence()->choices() !== $sequence->choices()) {
                $events->recordThatGeneratorsAreNondeterministic();

                continue;
            }

            $replays['numberOfReplays']++;
            $replays['counterexample'] = $counterexample;
            $replays['drawnValues']    = $drawnValues->exported();
            $replays['steps']          = $drawnValues->steps();
            $replays['events']         = $replayEvents;

            $replayFalsification = Falsification::of($example);

            if ($replayFalsification !== null && $replayFalsification->isSameAs($falsification)) {
                $replays['numberOfReproductions']++;
            }
        }

        return $replays;
    }

    /**
     * Reports a property as flaky whose example that falsified it did not
     * falsify it again when it was replayed, without shrinking its
     * counterexample, with the events of its last replay and with the failure
     * that was originally observed as the cause of the error.
     *
     * @param array{numberOfReplays: non-negative-int, numberOfReproductions: non-negative-int, counterexample: array<non-empty-string, string>, drawnValues: list<array{label: ?non-empty-string, value: string}>, steps: list<non-empty-string>, events: ?EventCollection} $replays
     */
    private function reportExampleThatDidNotFalsifyAgain(EventAggregator $events, Event\Emitter $emitter, ?Event\Code\Throwable $originalFailure, ChoiceSequence $sequence, array $replays): void
    {
        $numberOfReplays = $replays['numberOfReplays'];

        assert($numberOfReplays > 0);

        $flaky = Event\Code\ThrowableBuilder::from(FlakyPropertyException::exampleNotFalsifyingAgain($numberOfReplays));

        $error = new Event\Code\Throwable(
            $flaky->className(),
            $flaky->message(),
            $flaky->description(),
            $flaky->stackTrace(),
            $originalFailure,
        );

        $test                    = $this->test;
        $seed                    = $this->seed;
        $numberOfExamplesChecked = $this->statistics()->numberOfExamplesChecked();
        $counterexample          = $replays['counterexample'];
        $drawnValues             = $replays['drawnValues'];
        $steps                   = $replays['steps'];

        assert($test !== null);

        $this->report(
            $events,
            $emitter,
            $replays['events'],
            $error,
            false,
            new FalsificationReport(
                $seed,
                $numberOfExamplesChecked,
                0,
                0,
                false,
                false,
                self::counterexample($sequence->choices(), $counterexample, null, $drawnValues, $steps),
                [],
                $numberOfReplays,
                0,
            ),
        );
    }

    /**
     * Records in the choice tree that the example was generated and records a
     * PHPUnit warning when its generators did not draw the same choices as
     * before from the same choices.
     */
    private function exampleGenerated(ChoiceSource $source, EventAggregator $events): void
    {
        $source->exampleGenerated();

        if ($source->drewDifferentlyThanBefore()) {
            $events->recordThatGeneratorsAreNondeterministic();
        }
    }

    /**
     * Remembers the choice sequence of a shrinking attempt that falsified the
     * property in another way than the counterexample that is shrunk, unless
     * a simpler one is remembered for that way. An attempt that was aborted
     * because the deadline of the test run has passed, or that ended before
     * it was prepared, did not falsify the property.
     */
    private function rememberOtherFalsification(TestCase $example, Falsification $falsification, EventCollection $attemptEvents, ChoiceSequence $sequence): void
    {
        if (self::propertyExampleOf($example)->throwableThatEndedIt() instanceof TimeLimitExceededException || ExampleRunner::endedBeforeItWasPrepared($attemptEvents)) {
            return;
        }

        $description = $falsification->description();

        if (isset($this->otherFalsifications[$description]) && !$sequence->isSimplerThan($this->otherFalsifications[$description]['sequence'])) {
            return;
        }

        $this->otherFalsifications[$description] = ['falsification' => $falsification, 'sequence' => $sequence];
    }

    /**
     * Replays the simplest choice sequence of each of the other ways in which
     * the property was falsified while its counterexample was shrunk, so that
     * the arguments that it generates and the values that it draws are
     * exported. A choice sequence that does not falsify the property in the
     * same way again is not reported.
     *
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     *
     * @return list<Event\Code\OtherFalsification>
     */
    private function replayOtherFalsifications(EventAggregator $events, Event\Emitter $emitter, GeneratorDeriver $deriver, array $generators): array
    {
        $replayed = [];

        foreach ($this->otherFalsifications as $description => $other) {
            $source = ChoiceSource::replay($other['sequence'], ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

            try {
                $arguments = $this->exampleRunner()->handlingIssues(fn (): array => $this->exampleRunner()->generateArguments($generators, $source), null);
            } catch (Throwable) {
                continue;
            }

            $counterexample = $this->exampleRunner()->export($arguments, $source->constructions());
            $drawnValues    = DrawnValues::reportedFrom($source, $this->exampleRunner()->exporter($source->constructions()));
            $example        = $this->exampleRunner()->example($arguments, false, $drawnValues);

            [$replayEvents] = $this->exampleRunner()->run($example, $emitter, false);

            $events->recordShrinkingAttempt($replayEvents);

            $falsification = Falsification::of($example);

            if ($falsification === null || !$falsification->isSameAs($other['falsification'])) {
                continue;
            }

            $replayed[] = new Event\Code\OtherFalsification(
                $description,
                self::counterexample($source->sequence()->choices(), $counterexample, null, $drawnValues->exported(), $drawnValues->steps()),
            );
        }

        return $replayed;
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
            $timeout = self::propertyExampleOf($example)->throwableThatEndedIt();

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
            new FalsificationReport($seed, 0, 0, 0, false, false, self::counterexample([], $exportedArguments, $name, [], []), [], 0, 0),
        );
    }

    /**
     * @return list<array{label: non-empty-string, highestScore: float|int}>
     */
    private function highestTargetedScores(): array
    {
        if ($this->targetedSearch === null) {
            return [];
        }

        return $this->targetedSearch->highestScores();
    }

    /**
     * Whether an example was discarded because assume() was called with
     * false: an example that falsified the property or was aborted because
     * the deadline of the test run has passed was not.
     */
    private function wasRejected(TestCase $example, ?Falsification $falsification): bool
    {
        return $falsification === null &&
               !self::propertyExampleOf($example)->throwableThatEndedIt() instanceof TimeLimitExceededException &&
               self::propertyExampleOf($example)->wasRejected();
    }

    private function recordCheckedExample(EventAggregator $events, EventCollection $exampleEvents, ExampleRun $run): void
    {
        $this->statistics()->recordRunOfExample($run);

        $events->record($exampleEvents, true);
    }

    /**
     * Whether the property is reported with the events of an example that ran
     * and was not rejected, without the example falsifying it, so that no
     * further examples are checked:
     *
     * - an example that is aborted because the deadline of the test run has
     *   passed does not falsify the property
     * - an example that failed, errored, or exceeded its time limit without
     *   checking the property is reported without being shrunk, see
     *   wasAborted() and errorOfExampleThatEndsCheck()
     * - when an example is skipped or marked incomplete, so is the property
     *
     * Such an example is not counted as one of the examples for which the
     * property was checked, as it did not check the property.
     */
    private function endsCheckWithoutFalsification(TestCase $example, ?Falsification $falsification, EventCollection $exampleEvents): bool
    {
        if (self::propertyExampleOf($example)->throwableThatEndedIt() instanceof TimeLimitExceededException) {
            return true;
        }

        if ($falsification !== null) {
            return $this->wasAborted($example, $exampleEvents);
        }

        return $example->status()->isSkipped() || $example->status()->isIncomplete();
    }

    /**
     * The error of the property when an example ends the check without
     * falsifying it, or null when the events of the example have its outcome.
     *
     * An example that exceeds its time limit has no outcome among its events,
     * as the test runner reports neither an error nor a risky test for it:
     * when it is aborted before it was prepared, in setUp(), for instance, it
     * did not check the property, and exceeding the time limit is the error
     * of the property, like an error in setUp() is. Without this error, the
     * property would be reported as if it had passed.
     */
    private function errorOfExampleThatEndsCheck(TestCase $example, ?Falsification $falsification): ?Event\Code\Throwable
    {
        if ($falsification === null || !$falsification->isTimeout()) {
            return null;
        }

        $timeout = self::propertyExampleOf($example)->throwableThatEndedIt();

        assert($timeout !== null);

        return Event\Code\ThrowableBuilder::from($timeout);
    }

    /**
     * Whether an example that failed or errored did so without checking the
     * property, so that it is not a counterexample:
     *
     * - a state machine that cannot be run is a mistake in the test, so the
     *   error of the example is the error of the property
     * - an example that fails or errors before it is prepared, in setUp() or
     *   in another hook method that is called before the test method, did
     *   not call the test method with its arguments
     */
    private function wasAborted(TestCase $example, EventCollection $exampleEvents): bool
    {
        return self::propertyExampleOf($example)->throwableThatEndedIt() instanceof InvalidStateMachineException ||
               ExampleRunner::endedBeforeItWasPrepared($exampleEvents);
    }

    /**
     * @param bool                 $passed        whether the property passed, that is, whether all examples that were checked passed
     * @param ?FalsificationReport $falsification how the property was falsified, null when it was not falsified
     */
    private function report(EventAggregator $events, Event\Emitter $emitter, ?EventCollection $skeleton, ?Event\Code\Throwable $error, bool $passed, ?FalsificationReport $falsification): void
    {
        $test       = $this->test;
        $statistics = $this->statistics();

        assert($test !== null);

        $this->decideHeldBackCodeCoverage($events, $test);

        $events->dispatch(
            $skeleton,
            $error,
            $passed,
            $falsification,
            function () use ($emitter, $test, $statistics, $passed): void
            {
                foreach ($statistics->riskyMessages($passed, $this->requiredClassifications()) as $message) {
                    $emitter->testConsideredRisky($test, $message);
                }

                $emitter->testPropertyChecked(
                    $test,
                    $this->seed,
                    $statistics->numberOfExamplesChecked(),
                    $statistics->numberOfExplicitExamplesChecked(),
                    $statistics->numberOfExamplesRejected(),
                    $statistics->classifications(),
                    $statistics->observations(),
                    $this->highestTargetedScores(),
                );
            },
            $statistics->numberOfAssertions(),
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

        if ($this->statistics()->numberOfAssertions() === 0) {
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

        $this->statistics()->recordContributionToCodeCoverage($contributed);
    }

    /**
     * The explicit examples of a property whose requirements are not met are
     * not checked, because its first example is skipped.
     *
     * @return list<ExplicitExample>
     */
    private function explicitExamples(): array
    {
        $definition = $this->generatorResolver()->definition();

        if ($definition === null) {
            return [];
        }

        return $definition->explicitExamples();
    }

    private function shouldStop(): bool
    {
        return TimeLimitHandler::deadlineReached() || TestResultFacade::shouldStop();
    }

    private function generatorResolver(): GeneratorResolver
    {
        assert($this->generatorResolver !== null);

        return $this->generatorResolver;
    }

    /**
     * Creates the runner of the examples of the property when the property
     * starts running: when it is checked, or when the first example is run on
     * its own.
     */
    private function startRunning(TestCase $test, Event\Code\TestMethod $valueObject): void
    {
        assert($this->exampleFactory !== null);

        $this->test          = $valueObject;
        $this->exampleRunner = new ExampleRunner(
            $valueObject,
            TestRunner::shouldErrorHandlerBeUsed($test),
            $this->exampleFactory,
            $this->generatorResolver(),
            Event\Facade::instance(),
        );
    }

    private function exampleRunner(): ExampleRunner
    {
        assert($this->exampleRunner !== null);

        return $this->exampleRunner;
    }

    private function statistics(): PropertyStatistics
    {
        assert($this->statistics !== null);

        return $this->statistics;
    }

    /**
     * The classifications that #[RequireClassification] requires, none for a
     * property without a definition, whose examples are skipped.
     *
     * @return list<array{label: non-empty-string, minimumPercentage: float}>
     */
    private function requiredClassifications(): array
    {
        $definition = $this->generatorResolver()->definition();

        if ($definition === null) {
            return [];
        }

        return $definition->requiredClassifications();
    }

    /**
     * The example of the property that a test that this test suite created
     * for an example is.
     */
    private static function propertyExampleOf(TestCase $example): PropertyExample
    {
        $propertyExample = $example->propertyExample();

        assert($propertyExample !== null);

        return $propertyExample;
    }

    /**
     * @param list<non-negative-int>                               $choiceSequence
     * @param array<non-empty-string, string>                      $arguments
     * @param null|int|non-empty-string                            $explicitExample
     * @param list<array{label: ?non-empty-string, value: string}> $drawnValues
     * @param list<non-empty-string>                               $steps
     */
    private static function counterexample(array $choiceSequence, array $arguments, null|int|string $explicitExample, array $drawnValues, array $steps): Event\Code\Counterexample
    {
        $values = [];

        foreach ($drawnValues as $drawnValue) {
            $values[] = new Event\Code\DrawnValue($drawnValue['label'], $drawnValue['value']);
        }

        return new Event\Code\Counterexample($choiceSequence, $arguments, $explicitExample, $values, $steps);
    }
}
