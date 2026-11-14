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
use function min;
use Closure;
use PHPUnit\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TimeLimit\TimeLimitExceededException;
use SebastianBergmann\ChoiceSequence\ChoiceSequence;
use SebastianBergmann\ChoiceSequence\Shrinker;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use Throwable;

/**
 * Reports how a property was falsified: by an explicit example, whose
 * failure or error is reported as it is, or by a generated example, which is
 * replayed to tell whether it falsifies the property again, whose
 * counterexample is shrunk, and whose minimal counterexample is replayed, as
 * are the simplest counterexamples of the other ways in which the property
 * was falsified while it was shrunk.
 *
 * A generated example that falsified the property by triggering an issue,
 * and that does not trigger this issue when it is replayed, was not falsified
 * by the issue, which was triggered for another reason than the arguments of
 * the example, because a deprecated class was loaded when the example used it
 * first, for instance: the issue is reported for the property like every
 * other issue of its examples. When the replays do not falsify the property,
 * neither did the example. When they do, in another way, that way is the one
 * for which the counterexample is shrunk and that is reported.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class FalsificationReporter
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
    private readonly ExampleRunner $runner;
    private readonly EventAggregator $events;
    private readonly Event\Emitter $emitter;

    /**
     * @var non-negative-int
     */
    private readonly int $seed;

    /**
     * @var non-negative-int
     */
    private readonly int $maximumNumberOfShrinkingAttempts;

    /**
     * @var Closure(): bool
     */
    private readonly Closure $shouldStop;

    /**
     * @var Closure(?EventCollection, ?Event\Code\Throwable, null|FalsificationReport): void
     */
    private readonly Closure $report;

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
     * @param non-negative-int                                                                 $seed
     * @param non-negative-int                                                                 $maximumNumberOfShrinkingAttempts
     * @param Closure(): bool                                                                  $shouldStop                       whether no further examples are run because the test run is aborted
     * @param Closure(?EventCollection, ?Event\Code\Throwable, null|FalsificationReport): void $report                           reports the property, which did not pass, with the events of an example, an error, and how it was falsified
     */
    public function __construct(ExampleRunner $runner, EventAggregator $events, Event\Emitter $emitter, int $seed, int $maximumNumberOfShrinkingAttempts, Closure $shouldStop, Closure $report)
    {
        $this->runner                           = $runner;
        $this->events                           = $events;
        $this->emitter                          = $emitter;
        $this->seed                             = $seed;
        $this->maximumNumberOfShrinkingAttempts = $maximumNumberOfShrinkingAttempts;
        $this->shouldStop                       = $shouldStop;
        $this->report                           = $report;
    }

    /**
     * Replays the example that first falsified the property, shrinks its
     * counterexample, and reports how the property was falsified, with the
     * final replay of the minimal counterexample as the example whose events
     * are reported.
     *
     * @param array<non-empty-string, AbstractGenerator<mixed>> $generators
     * @param ?Event\Code\Throwable                             $originalFailure         the failure or error of the example that first falsified the property
     * @param ChoiceSequence                                    $sequence                the choices of the example that first falsified the property
     * @param non-negative-int                                  $numberOfExamplesChecked
     *
     * @throws Event\RuntimeException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws UnintentionallyCoveredCodeException
     *
     * @return bool whether the property was reported, which it is not when the example falsified it by triggering an issue and does not falsify it when it is replayed, so that further examples are to be checked
     */
    public function shrinkAndReport(GeneratorDeriver $deriver, array $generators, ?Event\Code\Throwable $originalFailure, Falsification $falsification, ChoiceSequence $sequence, int $numberOfExamplesChecked): bool
    {
        $replays = $this->replayFalsifyingExample($deriver, $generators, $falsification, $sequence);

        if ($replays === null) {
            return true;
        }

        $numberOfReplays       = $replays['numberOfReplays'];
        $numberOfReproductions = $replays['numberOfReproductions'];

        if ($numberOfReplays > 0 && $numberOfReproductions === 0) {
            if (!$falsification->isIssue()) {
                $this->reportExampleThatDidNotFalsifyAgain($originalFailure, $sequence, $replays, $numberOfExamplesChecked);

                return true;
            }

            if ($replays['otherFalsification'] === null) {
                return false;
            }

            // the issue was triggered for another reason than the arguments
            // of the example, which falsifies the property in another way
            // when it is replayed: that way is the one that is shrunk and
            // reported
            $falsification         = $replays['otherFalsification'];
            $numberOfReproductions = $replays['numberOfReproductionsOfOtherFalsification'];
        }

        $maximumNumberOfAttempts = $this->maximumNumberOfShrinkingAttempts;

        if ($falsification->isTimeout()) {
            $maximumNumberOfAttempts = min($maximumNumberOfAttempts, self::MAXIMUM_NUMBER_OF_SHRINKING_ATTEMPTS_AFTER_TIMEOUT);
        }

        $shrinker = new Shrinker(
            function (ChoiceSequence $candidate) use ($deriver, $generators, $falsification): ?ChoiceSequence
            {
                $source = ChoiceSource::replay($candidate, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

                try {
                    $arguments = $this->runner->handlingIssues(fn (): array => $this->runner->generateArguments($generators, $source), null);
                } catch (Throwable) {
                    return null;
                }

                $example = $this->runner->example($arguments, false, DrawnValues::from($source));

                [$attemptEvents] = $this->runner->run($example, $this->emitter, false);

                $this->events->recordShrinkingAttempt($attemptEvents);

                $candidateFalsification = $this->runner->falsificationOf($example, $attemptEvents);

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
            $this->shouldStop,
        );

        $result = $shrinker->shrink($sequence);
        $source = ChoiceSource::replay($result->sequence(), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

        try {
            $arguments = $this->runner->handlingIssues(fn (): array => $this->runner->generateArguments($generators, $source), $this->events);
        } catch (Throwable $t) {
            ($this->report)(null, Event\Code\ThrowableBuilder::from($t), null);

            return true;
        }

        $counterexample = $this->runner->export($arguments, $source->constructions());
        $formatted      = $this->runner->format($arguments);
        $drawnValues    = DrawnValues::reportedFrom($source, $this->runner->exporter($source->constructions()));
        $replay         = $this->runner->example($arguments, true, $drawnValues);
        [$replayEvents] = $this->runner->run($replay, $this->emitter, false);

        $replayFalsification = null;
        $aborted             = ExampleRunner::propertyExampleOf($replay)->throwableThatEndedIt() instanceof TimeLimitExceededException;

        if (!$aborted) {
            $replayFalsification = $this->runner->falsificationOf($replay, $replayEvents);
        }

        // the example that falsified the property by triggering an issue was
        // not replayed before its counterexample was shrunk, because the test
        // run is aborted, for instance, no shrinking attempt triggered the
        // issue, and the example does not falsify the property now that it is
        // replayed
        if (!$aborted && $replayFalsification === null && $falsification->isIssue() && $numberOfReproductions === 0 && $result->numberOfSteps() === 0) {
            $this->events->recordShrinkingAttempt($replayEvents);

            return false;
        }

        // the issues of the final replay are reported like those of the
        // examples that were checked, the replay being the skeleton does not
        // report them
        $this->events->record($replayEvents, true);

        $error   = null;
        $failure = null;

        // a final replay that is aborted because the deadline of the test run
        // has passed is reported with that error, like any other example that
        // is aborted by it, and with the counterexample, which falsified the
        // property while it was shrunk
        if (!$aborted) {
            if ($replayFalsification === null) {
                $error = $this->flaky(FlakyPropertyException::minimalCounterexampleNotFalsifyingAgain(), $originalFailure);
            } elseif ($replayFalsification->isTimeout()) {
                $timeout = ExampleRunner::propertyExampleOf($replay)->throwableThatEndedIt();

                assert($timeout !== null);

                $error = Event\Code\ThrowableBuilder::from($timeout);
            } elseif (!$replayFalsification->isSameAs($falsification)) {
                // the counterexample was shrunk for the way in which the
                // property was first falsified, so reporting the failure of
                // the replay with it would misrepresent both
                $error = $this->flaky(
                    FlakyPropertyException::minimalCounterexampleFalsifyingDifferently($falsification->description(), $replayFalsification->description()),
                    $originalFailure,
                );
            } elseif ($replayFalsification->isIssue()) {
                $failure = self::failureFor($replayFalsification);
            }
        }

        $exportedDrawnValues = $drawnValues->exported();
        $steps               = $drawnValues->steps();
        $otherFalsifications = $this->replayOtherFalsifications($deriver, $generators);

        ($this->report)(
            $replayEvents,
            $error,
            new FalsificationReport(
                $this->seed,
                $numberOfExamplesChecked,
                $result->numberOfAttempts(),
                $result->numberOfSteps(),
                $result->budgetExhausted(),
                $result->interrupted(),
                self::counterexample($result->sequence()->choices(), $counterexample, null, $exportedDrawnValues, $steps, $formatted),
                $otherFalsifications,
                $numberOfReplays,
                $numberOfReproductions,
                $failure,
            ),
        );

        return true;
    }

    /**
     * Reports a property that was falsified by an explicit example with the
     * failure or error of that example, which is not shrunk, and with its
     * arguments.
     *
     * @param array<non-empty-string, string> $exportedArguments the arguments of the example, exported as PHP code
     * @param ?non-empty-string               $formatted         what the counterexample formatter returned for the arguments of the example
     */
    public function reportFalsificationByExplicitExample(TestCase $example, Falsification $falsification, EventCollection $exampleEvents, ExplicitExample $explicitExample, array $exportedArguments, ?string $formatted = null): void
    {
        $error   = null;
        $failure = null;

        if ($falsification->isIssue()) {
            $failure = self::failureFor($falsification);
        }

        // an example that exceeded its time limit is reported with that error
        // rather than as risky, because it falsified the property
        if ($falsification->isTimeout()) {
            $timeout = ExampleRunner::propertyExampleOf($example)->throwableThatEndedIt();

            assert($timeout !== null);

            $error = Event\Code\ThrowableBuilder::from($timeout);
        }

        $name = $explicitExample->name();

        ($this->report)(
            $exampleEvents,
            $error,
            new FalsificationReport($this->seed, 0, 0, 0, false, false, self::counterexample([], $exportedArguments, $name, [], [], $formatted), [], 0, 0, $failure),
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
     * @return ?array{numberOfReplays: non-negative-int, numberOfReproductions: non-negative-int, otherFalsification: ?Falsification, numberOfReproductionsOfOtherFalsification: non-negative-int, counterexample: array<non-empty-string, string>, formatted: ?non-empty-string, drawnValues: list<array{label: ?non-empty-string, value: string}>, steps: list<non-empty-string>, events: ?EventCollection} the replays, with the arguments and values of the last replay that was counted, exported, and its events, or null when the arguments could not be generated again, which was reported
     */
    private function replayFalsifyingExample(GeneratorDeriver $deriver, array $generators, Falsification $falsification, ChoiceSequence $sequence): ?array
    {
        $replays = [
            'numberOfReplays'                           => 0,
            'numberOfReproductions'                     => 0,
            'otherFalsification'                        => null,
            'numberOfReproductionsOfOtherFalsification' => 0,
            'counterexample'                            => [],
            'formatted'                                 => null,
            'drawnValues'                               => [],
            'steps'                                     => [],
            'events'                                    => null,
        ];

        if ($falsification->isTimeout()) {
            return $replays;
        }

        for ($replay = 1; $replay <= self::NUMBER_OF_REPLAYS_OF_FALSIFYING_EXAMPLE && !($this->shouldStop)(); $replay++) {
            $source = ChoiceSource::replay($sequence, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

            try {
                $arguments = $this->runner->handlingIssues(fn (): array => $this->runner->generateArguments($generators, $source), null);
            } catch (Throwable $t) {
                ($this->report)(null, Event\Code\ThrowableBuilder::from($t), null);

                return null;
            }

            $counterexample = $this->runner->export($arguments, $source->constructions());
            $formatted      = $this->runner->format($arguments);
            $drawnValues    = DrawnValues::reportedFrom($source, $this->runner->exporter($source->constructions()));
            $example        = $this->runner->example($arguments, false, $drawnValues);

            [$replayEvents] = $this->runner->run($example, $this->emitter, false);

            $this->events->recordShrinkingAttempt($replayEvents);

            if (ExampleRunner::propertyExampleOf($example)->throwableThatEndedIt() instanceof TimeLimitExceededException) {
                break;
            }

            if ($source->sequence()->choices() !== $sequence->choices()) {
                $this->events->recordThatGeneratorsAreNondeterministic();

                continue;
            }

            $replays['numberOfReplays']++;
            $replays['counterexample'] = $counterexample;
            $replays['formatted']      = $formatted;
            $replays['drawnValues']    = $drawnValues->exported();
            $replays['steps']          = $drawnValues->steps();
            $replays['events']         = $replayEvents;

            $replayFalsification = $this->runner->falsificationOf($example, $replayEvents);

            if ($replayFalsification === null) {
                continue;
            }

            if ($replayFalsification->isSameAs($falsification)) {
                $replays['numberOfReproductions']++;

                continue;
            }

            if ($replays['otherFalsification'] === null) {
                $replays['otherFalsification'] = $replayFalsification;
            }

            if ($replayFalsification->isSameAs($replays['otherFalsification'])) {
                $replays['numberOfReproductionsOfOtherFalsification']++;
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
     * @param array{numberOfReplays: non-negative-int, numberOfReproductions: non-negative-int, otherFalsification: ?Falsification, numberOfReproductionsOfOtherFalsification: non-negative-int, counterexample: array<non-empty-string, string>, formatted: ?non-empty-string, drawnValues: list<array{label: ?non-empty-string, value: string}>, steps: list<non-empty-string>, events: ?EventCollection} $replays
     * @param non-negative-int                                                                                                                                                                                                                                                                                                                                                                              $numberOfExamplesChecked
     */
    private function reportExampleThatDidNotFalsifyAgain(?Event\Code\Throwable $originalFailure, ChoiceSequence $sequence, array $replays, int $numberOfExamplesChecked): void
    {
        $numberOfReplays = $replays['numberOfReplays'];

        assert($numberOfReplays > 0);

        $error = $this->flaky(FlakyPropertyException::exampleNotFalsifyingAgain($numberOfReplays), $originalFailure);

        $counterexample = $replays['counterexample'];
        $drawnValues    = $replays['drawnValues'];
        $steps          = $replays['steps'];

        ($this->report)(
            $replays['events'],
            $error,
            new FalsificationReport(
                $this->seed,
                $numberOfExamplesChecked,
                0,
                0,
                false,
                false,
                self::counterexample($sequence->choices(), $counterexample, null, $drawnValues, $steps, $replays['formatted']),
                [],
                $numberOfReplays,
                0,
            ),
        );
    }

    /**
     * The error of a property that is flaky, with the failure that was
     * originally observed as its cause.
     */
    private function flaky(FlakyPropertyException $exception, ?Event\Code\Throwable $originalFailure): Event\Code\Throwable
    {
        $flaky = Event\Code\ThrowableBuilder::from($exception);

        return new Event\Code\Throwable(
            $flaky->className(),
            $flaky->message(),
            $flaky->description(),
            $flaky->stackTrace(),
            $originalFailure,
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
        if (ExampleRunner::propertyExampleOf($example)->throwableThatEndedIt() instanceof TimeLimitExceededException || ExampleRunner::endedBeforeItWasPrepared($attemptEvents)) {
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
    private function replayOtherFalsifications(GeneratorDeriver $deriver, array $generators): array
    {
        $replayed = [];

        foreach ($this->otherFalsifications as $description => $other) {
            $source = ChoiceSource::replay($other['sequence'], ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver);

            try {
                $arguments = $this->runner->handlingIssues(fn (): array => $this->runner->generateArguments($generators, $source), null);
            } catch (Throwable) {
                continue;
            }

            $counterexample = $this->runner->export($arguments, $source->constructions());
            $formatted      = $this->runner->format($arguments);
            $drawnValues    = DrawnValues::reportedFrom($source, $this->runner->exporter($source->constructions()));
            $example        = $this->runner->example($arguments, false, $drawnValues);

            [$replayEvents] = $this->runner->run($example, $this->emitter, false);

            $this->events->recordShrinkingAttempt($replayEvents);

            $falsification = $this->runner->falsificationOf($example, $replayEvents);

            if ($falsification === null || !$falsification->isSameAs($other['falsification'])) {
                continue;
            }

            $replayed[] = new Event\Code\OtherFalsification(
                $description,
                self::counterexample($source->sequence()->choices(), $counterexample, null, $drawnValues->exported(), $drawnValues->steps(), $formatted),
            );
        }

        return $replayed;
    }

    /**
     * The failure that is reported for a property that was falsified by an
     * issue: the example that triggered the issue has no failure or error of
     * its own, and the issue alone would be reported without the
     * counterexample.
     */
    private static function failureFor(Falsification $falsification): Event\Code\Throwable
    {
        return Event\Code\ThrowableBuilder::from(new AssertionFailedError($falsification->message()));
    }

    /**
     * @param list<non-negative-int>                               $choiceSequence
     * @param array<non-empty-string, string>                      $arguments
     * @param null|int|non-empty-string                            $explicitExample
     * @param list<array{label: ?non-empty-string, value: string}> $drawnValues
     * @param list<non-empty-string>                               $steps
     * @param ?non-empty-string                                    $formatted       what the counterexample formatter returned for the arguments
     */
    private static function counterexample(array $choiceSequence, array $arguments, null|int|string $explicitExample, array $drawnValues, array $steps, ?string $formatted = null): Event\Code\Counterexample
    {
        $values = [];

        foreach ($drawnValues as $drawnValue) {
            $values[] = new Event\Code\DrawnValue($drawnValue['label'], $drawnValue['value']);
        }

        return new Event\Code\Counterexample($choiceSequence, $arguments, $explicitExample, $values, $steps, $formatted);
    }
}
