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
use function array_shift;
use function array_values;
use function assert;
use function count;
use function implode;
use function intdiv;
use function min;
use function sprintf;
use function usort;
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
use PHPUnit\Framework\PropertyBased\DrawnValues;
use PHPUnit\Framework\PropertyBased\EventAggregator;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\ExplicitExample;
use PHPUnit\Framework\PropertyBased\Falsification;
use PHPUnit\Framework\PropertyBased\FlakyPropertyException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\GeneratorMethod;
use PHPUnit\Framework\PropertyBased\InvalidPropertyException;
use PHPUnit\Framework\PropertyBased\Observations;
use PHPUnit\Framework\PropertyBased\PropertyDefinition;
use PHPUnit\Framework\PropertyBased\PropertyDefinitionBuilder;
use PHPUnit\Framework\PropertyBased\RejectionReason;
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

    /**
     * How often the example that first falsified a property is replayed
     * before its counterexample is shrunk, to tell whether it falsifies the
     * property again.
     */
    private const int NUMBER_OF_REPLAYS_OF_FALSIFYING_EXAMPLE = 4;
    private ?PropertyDefinition $definition                   = null;

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
     * How many of the generated examples were rejected for each reason, in
     * the order in which the reasons first occurred.
     *
     * @var list<array{reason: RejectionReason, numberOfExamples: positive-int}>
     */
    private array $rejections = [];

    /**
     * The reason for which examples were rejected whose advice is given
     * because no further examples were generated once too many examples were
     * rejected before enough were checked, null when this did not happen.
     */
    private ?RejectionReason $unhealthyBecauseOf = null;

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
     * The values that the generated examples that were checked observed,
     * null when none was checked yet.
     */
    private ?Observations $observations = null;

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
     * Whether a PHPUnit warning was recorded because the generators of the
     * property do not generate the same values from the same choices.
     */
    private bool $warnedAboutNondeterministicGenerators = false;

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
     * The test run history from which the counterexamples that were
     * remembered when the property was last falsified are taken.
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
        $remembered = [];

        if ($this->testRunHistory !== null) {
            $remembered = $this->testRunHistory->counterexamples(TestRunHistoryId::fromReorderable($test));
        }

        for ($i = 1; $i <= $this->maximumNumberOfExamplesGenerated(); $i++) {
            if ($this->numberOfExamplesChecked === $this->numberOfExamples) {
                break;
            }

            if ($tree->isExhausted()) {
                $this->exhausted = true;

                break;
            }

            $this->unhealthyBecauseOf = $this->reasonWhyExamplesAreNoLongerGenerated();

            if ($this->unhealthyBecauseOf !== null) {
                break;
            }

            if ($attempted && $this->shouldStop()) {
                $this->aborted = true;

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
                $source = ChoiceSource::random($randomizer, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver, $tree);
            }

            try {
                $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), $events);
            } catch (ExampleRejectedException $e) {
                // the same choices are rejected again, so they are not drawn
                // again
                $this->exampleGenerated($source, $events, $emitter);

                $this->countRejection($e->reason());

                continue;
            } catch (Throwable $t) {
                $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

                return;
            }

            $example               = $this->example($arguments, false, DrawnValues::from($source));
            [$exampleEvents, $run] = $this->runExample($example, $emitter, true);
            $falsification         = Falsification::of($example);

            // the same choices generate the same arguments and draw the same
            // values, or are rejected again, so they are not drawn again; the
            // example draws choices with draw() until it ends
            $this->exampleGenerated($source, $events, $emitter);

            if ($this->wasRejected($example, $falsification)) {
                $this->countRejection($example->rejectionReasonOfPropertyExample());

                $events->record($exampleEvents, false);

                continue;
            }

            $this->numberOfExamplesChecked++;

            $this->countClassifications($example->classificationsOfPropertyExample());
            $this->observations()->record($example->observationsOfPropertyExample());
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
                    $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), null);
                } catch (Throwable) {
                    return null;
                }

                $example = $this->example($arguments, false, DrawnValues::from($source));

                [$attemptEvents] = $this->runExample($example, $emitter, false);

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
            $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), $events);
        } catch (Throwable $t) {
            $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

            return;
        }

        $counterexample = $this->export($arguments, $source->constructions());
        $drawnValues    = DrawnValues::reportedFrom($source, $this->exporter($source->constructions()));
        $replay         = $this->example($arguments, true, $drawnValues);
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
                $timeout = $replay->throwableThatEndedTheTest();

                assert($timeout !== null);

                $error = Event\Code\ThrowableBuilder::from($timeout);
            }
        }

        $test                = $this->test;
        $seed                = $this->seed;
        $exportedDrawnValues = $drawnValues->exported();
        $otherFalsifications = $this->replayOtherFalsifications($events, $emitter, $deriver, $generators);

        assert($test !== null);

        $this->report(
            $events,
            $emitter,
            $replayEvents,
            $error,
            false,
            static function () use ($emitter, $test, $seed, $numberOfExamplesChecked, $result, $counterexample, $exportedDrawnValues, $otherFalsifications, $numberOfReplays, $numberOfReproductions): void
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
                    $exportedDrawnValues,
                    $otherFalsifications,
                    $numberOfReplays,
                    $numberOfReproductions,
                );
            },
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
     * @return ?array{numberOfReplays: non-negative-int, numberOfReproductions: non-negative-int, counterexample: array<non-empty-string, string>, drawnValues: list<array{label: ?non-empty-string, value: string}>, events: ?EventCollection} the replays, with the arguments and values of the last replay that was counted, exported, and its events, or null when the arguments could not be generated again, which was reported
     */
    private function replayFalsifyingExample(EventAggregator $events, Event\Emitter $emitter, GeneratorDeriver $deriver, array $generators, Falsification $falsification, ChoiceSequence $sequence): ?array
    {
        $replays = [
            'numberOfReplays'       => 0,
            'numberOfReproductions' => 0,
            'counterexample'        => [],
            'drawnValues'           => [],
            'events'                => null,
        ];

        if ($falsification->isTimeout()) {
            return $replays;
        }

        for ($replay = 1; $replay <= self::NUMBER_OF_REPLAYS_OF_FALSIFYING_EXAMPLE && !$this->shouldStop(); $replay++) {
            $source = ChoiceSource::replay($sequence, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

            try {
                $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), null);
            } catch (Throwable $t) {
                $this->report($events, $emitter, null, Event\Code\ThrowableBuilder::from($t), false, null);

                return null;
            }

            $counterexample = $this->export($arguments, $source->constructions());
            $drawnValues    = DrawnValues::reportedFrom($source, $this->exporter($source->constructions()));
            $example        = $this->example($arguments, false, $drawnValues);

            [$replayEvents] = $this->runExample($example, $emitter, false);

            $events->recordShrinkingAttempt($replayEvents);

            if ($example->throwableThatEndedTheTest() instanceof TimeLimitExceededException) {
                break;
            }

            if ($source->sequence()->choices() !== $sequence->choices()) {
                $this->warnAboutNondeterministicGenerators($events, $emitter);

                continue;
            }

            $replays['numberOfReplays']++;
            $replays['counterexample'] = $counterexample;
            $replays['drawnValues']    = $drawnValues->exported();
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
     * @param array{numberOfReplays: non-negative-int, numberOfReproductions: non-negative-int, counterexample: array<non-empty-string, string>, drawnValues: list<array{label: ?non-empty-string, value: string}>, events: ?EventCollection} $replays
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
        $numberOfExamplesChecked = $this->numberOfExamplesChecked;
        $counterexample          = $replays['counterexample'];
        $drawnValues             = $replays['drawnValues'];

        assert($test !== null);

        $this->report(
            $events,
            $emitter,
            $replays['events'],
            $error,
            false,
            static function () use ($emitter, $test, $seed, $numberOfExamplesChecked, $sequence, $counterexample, $drawnValues, $numberOfReplays): void
            {
                assert($numberOfExamplesChecked > 0);

                $emitter->testPropertyFalsified(
                    $test,
                    $seed,
                    $numberOfExamplesChecked,
                    0,
                    0,
                    false,
                    false,
                    $sequence->choices(),
                    $counterexample,
                    null,
                    $drawnValues,
                    [],
                    $numberOfReplays,
                    0,
                );
            },
        );
    }

    /**
     * Records in the choice tree that the example was generated and records a
     * PHPUnit warning when its generators did not draw the same choices as
     * before from the same choices.
     */
    private function exampleGenerated(ChoiceSource $source, EventAggregator $events, Event\Emitter $emitter): void
    {
        $source->exampleGenerated();

        if ($source->drewDifferentlyThanBefore()) {
            $this->warnAboutNondeterministicGenerators($events, $emitter);
        }
    }

    /**
     * Records a PHPUnit warning, once per property, because its generators
     * did not generate the same arguments and values from the same choices:
     * its examples can then be generated more than once, and its
     * counterexamples cannot be replayed and shrunk reliably.
     */
    private function warnAboutNondeterministicGenerators(EventAggregator $events, Event\Emitter $emitter): void
    {
        if ($this->warnedAboutNondeterministicGenerators) {
            return;
        }

        $this->warnedAboutNondeterministicGenerators = true;

        $this->recordPhpunitWarning(
            $events,
            $emitter,
            'The generators of this property did not generate the same values from the same choices, so its examples may be generated more than once and its counterexamples may not be reproducible; values must not depend on anything but the choices they are generated from',
        );
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
        if ($example->throwableThatEndedTheTest() instanceof TimeLimitExceededException || $this->endedBeforeItWasPrepared($attemptEvents)) {
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
     * @return list<array{description: non-empty-string, choiceSequence: list<non-negative-int>, counterexample: array<non-empty-string, string>, drawnValues: list<array{label: ?non-empty-string, value: string}>}>
     */
    private function replayOtherFalsifications(EventAggregator $events, Event\Emitter $emitter, GeneratorDeriver $deriver, array $generators): array
    {
        $replayed = [];

        foreach ($this->otherFalsifications as $description => $other) {
            $source = ChoiceSource::replay($other['sequence'], ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

            try {
                $arguments = $this->handlingIssues(fn (): array => $this->generateArguments($generators, $source), null);
            } catch (Throwable) {
                continue;
            }

            $counterexample = $this->export($arguments, $source->constructions());
            $drawnValues    = DrawnValues::reportedFrom($source, $this->exporter($source->constructions()));
            $example        = $this->example($arguments, false, $drawnValues);

            [$replayEvents] = $this->runExample($example, $emitter, false);

            $events->recordShrinkingAttempt($replayEvents);

            $falsification = Falsification::of($example);

            if ($falsification === null || !$falsification->isSameAs($other['falsification'])) {
                continue;
            }

            $replayed[] = [
                'description'    => $description,
                'choiceSequence' => $source->sequence()->choices(),
                'counterexample' => $counterexample,
                'drawnValues'    => $drawnValues->exported(),
            ];
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
                $emitter->testPropertyFalsified($test, $seed, 0, 0, 0, false, false, [], $exportedArguments, $name, [], [], 0, 0);
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

    private function observations(): Observations
    {
        if ($this->observations === null) {
            $this->observations = new Observations;
        }

        return $this->observations;
    }

    private function countRejection(RejectionReason $reason): void
    {
        $this->numberOfExamplesRejected++;

        foreach ($this->rejections as $index => $rejection) {
            if ($rejection['reason'] === $reason) {
                $this->rejections[$index]['numberOfExamples']++;

                return;
            }
        }

        $this->rejections[] = ['reason' => $reason, 'numberOfExamples' => 1];
    }

    /**
     * Returns, when no further examples are generated because too many
     * examples were rejected before enough examples were checked, the reason
     * for which examples were rejected whose advice is given, and null
     * otherwise.
     */
    private function reasonWhyExamplesAreNoLongerGenerated(): ?RejectionReason
    {
        $configuration = ConfigurationRegistry::get();

        if ($this->numberOfExamplesChecked >= $configuration->examplesCheckedUntilHealthIsAssumed() || $this->rejections === []) {
            return null;
        }

        if ($this->numberOfExamplesRejected >= $configuration->maximumRejectedExamplesUntilHealthIsAssumed()) {
            return $this->mostFrequentRejectionReason();
        }

        $numberOfTooLargeExamples = 0;
        $mostFrequentTooLarge     = null;

        foreach ($this->rejectionsByFrequency() as $rejection) {
            if (!$rejection['reason']->meansTooLarge()) {
                continue;
            }

            $numberOfTooLargeExamples += $rejection['numberOfExamples'];

            if ($mostFrequentTooLarge === null) {
                $mostFrequentTooLarge = $rejection['reason'];
            }
        }

        if ($numberOfTooLargeExamples >= $configuration->maximumTooLargeExamplesUntilHealthIsAssumed()) {
            return $mostFrequentTooLarge;
        }

        return null;
    }

    /**
     * Describes how many of the generated examples were rejected and why, for
     * instance "12 examples were rejected (10 by assume(), 2 because no value
     * that satisfies a filter was generated)", with the most frequent reason
     * first.
     *
     * @return non-empty-string
     */
    private function rejectedExamples(): string
    {
        $rejections = $this->rejectionsByFrequency();

        if ($this->numberOfExamplesRejected === 1) {
            $buffer = '1 example was rejected';
        } else {
            $buffer = sprintf('%d examples were rejected', $this->numberOfExamplesRejected);
        }

        if (count($rejections) === 1) {
            $buffer .= ' ' . $rejections[0]['reason']->description();
        } else {
            $reasons = [];

            foreach ($rejections as $rejection) {
                $reasons[] = $rejection['numberOfExamples'] . ' ' . $rejection['reason']->description();
            }

            $buffer .= ' (' . implode(', ', $reasons) . ')';
        }

        return $buffer;
    }

    private function mostFrequentRejectionReason(): RejectionReason
    {
        return $this->rejectionsByFrequency()[0]['reason'];
    }

    /**
     * @return non-empty-list<array{reason: RejectionReason, numberOfExamples: positive-int}>
     */
    private function rejectionsByFrequency(): array
    {
        $rejections = $this->rejections;

        assert($rejections !== []);

        // the sort is stable, so reasons that occurred equally often stay in
        // the order in which they first occurred
        usort(
            $rejections,
            static fn (array $a, array $b): int => $b['numberOfExamples'] <=> $a['numberOfExamples'],
        );

        return $rejections;
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
                    $this->observations()->statistics(),
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
        if ($this->unhealthyBecauseOf !== null) {
            $messages[] = sprintf(
                'This property was checked for only %d of %d examples because generating examples was stopped after %s while fewer than %d examples were checked; %s',
                $this->numberOfExamplesChecked,
                $this->numberOfExamples,
                $this->rejectedExamples(),
                ConfigurationRegistry::get()->examplesCheckedUntilHealthIsAssumed(),
                $this->unhealthyBecauseOf->advice(),
            );
        } elseif ($this->numberOfExamplesChecked < $this->numberOfExamples &&
            ($this->numberOfExamplesChecked + $this->numberOfExamplesRejected === $this->maximumNumberOfExamplesGenerated() ||
             ($this->exhausted && $this->numberOfExamplesChecked === 0))) {
            $messages[] = sprintf(
                'This property was checked for only %d of %d examples, %s; %s',
                $this->numberOfExamplesChecked,
                $this->numberOfExamples,
                $this->rejectedExamples(),
                $this->mostFrequentRejectionReason()->advice(),
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
        // the examples of a property without a definition are skipped, so the
        // property does not pass and this is not asked for
        if ($this->definition === null) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
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

        foreach ($generators as $parameterName => $generator) {
            $arguments[$parameterName] = $generator->generate($source);
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
    private function exporter(Constructions $constructions): CounterexampleExporter
    {
        assert($this->definition !== null);

        return new CounterexampleExporter(
            NameScope::forMethod(new ReflectionMethod($this->definition->className(), $this->definition->methodName())),
            $constructions,
        );
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     * @param bool                           $failureIsReported whether the failure or error of the example is the one that is reported for the property, which it is for the final replay of the minimal counterexample and for an explicit example
     * @param ?DrawnValues                   $drawnValues       from which the example draws values with draw(), null for an explicit example
     */
    private function example(array $arguments, bool $failureIsReported = false, ?DrawnValues $drawnValues = null): TestCase
    {
        assert($this->exampleFactory !== null);

        $example = ($this->exampleFactory)();

        $example->setPropertyExample(array_values($arguments), $failureIsReported, $drawnValues);

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
                $emitter->testPreparationStarted(
                    $example->valueObjectForEvents(),
                );

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
