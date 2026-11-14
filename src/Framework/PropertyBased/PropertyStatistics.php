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

use const PHP_INT_MAX;
use function assert;
use function count;
use function implode;
use function intdiv;
use function sprintf;
use function usort;
use PHPUnit\Event\Code\Classification;
use PHPUnit\Event\Code\ObservedValues;
use PHPUnit\Framework\TestRunner\ExampleRun;
use PHPUnit\Framework\TestRunner\RiskyTestChecks;

/**
 * What the examples of a property that were generated and checked while the
 * property was checked amount to: how many were checked and rejected, and
 * why, how they were classified, what they observed, and the assertions that
 * they performed. From these it decides whether further examples are
 * generated and whether the property is considered risky, and why.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PropertyStatistics
{
    /**
     * Examples are generated until the number of examples has been checked
     * or this many times the number of examples has been generated.
     */
    private const int MAXIMUM_NUMBER_OF_EXAMPLES_GENERATED_PER_EXAMPLE = 10;

    /**
     * @var positive-int
     */
    private readonly int $numberOfExamples;

    /**
     * @var non-negative-int
     */
    private readonly int $examplesCheckedUntilHealthIsAssumed;

    /**
     * @var positive-int
     */
    private readonly int $maximumRejectedExamplesUntilHealthIsAssumed;

    /**
     * @var positive-int
     */
    private readonly int $maximumTooLargeExamplesUntilHealthIsAssumed;
    private readonly bool $reportUselessTests;

    /**
     * @var non-negative-int
     */
    private int $numberOfExamplesChecked = 0;

    /**
     * @var non-negative-int
     */
    private int $numberOfExamplesRejected = 0;

    /**
     * How many of the generated examples that were checked ran a state
     * machine, and how many of these took no step because none of its rules
     * could be applied.
     *
     * @var non-negative-int
     */
    private int $numberOfExamplesThatRanAStateMachine = 0;

    /**
     * @var non-negative-int
     */
    private int $numberOfExamplesWhoseStateMachineTookNoStep = 0;

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
     * The values that the generated examples that were checked observed.
     */
    private readonly Observations $observations;

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
     * @param positive-int     $numberOfExamples                            the number of generated examples for which the property is to be checked
     * @param non-negative-int $examplesCheckedUntilHealthIsAssumed
     * @param positive-int     $maximumRejectedExamplesUntilHealthIsAssumed
     * @param positive-int     $maximumTooLargeExamplesUntilHealthIsAssumed
     */
    public function __construct(int $numberOfExamples, int $examplesCheckedUntilHealthIsAssumed, int $maximumRejectedExamplesUntilHealthIsAssumed, int $maximumTooLargeExamplesUntilHealthIsAssumed, bool $reportUselessTests)
    {
        $this->numberOfExamples                            = $numberOfExamples;
        $this->examplesCheckedUntilHealthIsAssumed         = $examplesCheckedUntilHealthIsAssumed;
        $this->maximumRejectedExamplesUntilHealthIsAssumed = $maximumRejectedExamplesUntilHealthIsAssumed;
        $this->maximumTooLargeExamplesUntilHealthIsAssumed = $maximumTooLargeExamplesUntilHealthIsAssumed;
        $this->reportUselessTests                          = $reportUselessTests;
        $this->observations                                = new Observations;
    }

    /**
     * @return positive-int
     */
    public function maximumNumberOfExamplesGenerated(): int
    {
        // the number of examples is not limited, so the product could exceed
        // the largest integer
        if ($this->numberOfExamples > intdiv(PHP_INT_MAX, self::MAXIMUM_NUMBER_OF_EXAMPLES_GENERATED_PER_EXAMPLE)) {
            return PHP_INT_MAX;
        }

        return $this->numberOfExamples * self::MAXIMUM_NUMBER_OF_EXAMPLES_GENERATED_PER_EXAMPLE;
    }

    /**
     * Whether the property was checked for as many generated examples as it
     * is to be checked for.
     */
    public function allExamplesWereChecked(): bool
    {
        return $this->numberOfExamplesChecked === $this->numberOfExamples;
    }

    /**
     * Whether the property was checked for at least half of the generated
     * examples for which it is to be checked.
     */
    public function halfOfTheExamplesWereChecked(): bool
    {
        return $this->numberOfExamplesChecked >= intdiv($this->numberOfExamples, 2);
    }

    /**
     * Whether no further examples are generated because too many examples
     * were rejected before enough examples were checked. The reason for which
     * examples were rejected whose advice is given is remembered for the
     * message with which the property is considered risky.
     */
    public function tooManyExamplesWereRejected(): bool
    {
        $this->unhealthyBecauseOf = $this->reasonWhyExamplesAreNoLongerGenerated();

        return $this->unhealthyBecauseOf !== null;
    }

    public function recordThatTheTestRunWasAborted(): void
    {
        $this->aborted = true;
    }

    public function recordThatEveryExampleWasGenerated(): void
    {
        $this->exhausted = true;
    }

    public function recordRejectedExample(RejectionReason $reason): void
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
     * Records the assertions that an example that was checked performed, and
     * whether it contributed to code coverage. This is recorded for an
     * example that is not counted as one of the examples for which the
     * property was checked, because it was skipped, for instance, as well.
     */
    public function recordRunOfExample(ExampleRun $run): void
    {
        $this->numberOfAssertions += $run->numberOfAssertions();

        if ($run->doesNotPerformAssertions()) {
            $this->doesNotPerformAssertions = true;
        }

        $this->recordContributionToCodeCoverage($run->contributedToCodeCoverage());
    }

    /**
     * @param ?bool $contributed whether code coverage data that was collected for the property contributed to code coverage, null when this was not checked
     */
    public function recordContributionToCodeCoverage(?bool $contributed): void
    {
        if ($contributed !== null && $this->contributedToCodeCoverage !== true) {
            $this->contributedToCodeCoverage = $contributed;
        }
    }

    public function recordCheckedExplicitExample(): void
    {
        $this->numberOfExplicitExamplesChecked++;
    }

    public function recordCheckedGeneratedExample(PropertyExample $example): void
    {
        $this->numberOfExamplesChecked++;

        $this->countClassifications($example->classifications());
        $this->countStepsOfStateMachine($example->numberOfStepsOfStateMachine());
        $this->observations->record($example->observations());
    }

    /**
     * @return non-negative-int
     */
    public function numberOfExamplesChecked(): int
    {
        return $this->numberOfExamplesChecked;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfExplicitExamplesChecked(): int
    {
        return $this->numberOfExplicitExamplesChecked;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfExamplesRejected(): int
    {
        return $this->numberOfExamplesRejected;
    }

    /**
     * @return list<Classification>
     */
    public function classifications(): array
    {
        $classifications = [];

        foreach ($this->classifications as $classification) {
            $classifications[] = new Classification($classification['label'], $classification['numberOfExamples']);
        }

        return $classifications;
    }

    /**
     * @return list<ObservedValues>
     */
    public function observations(): array
    {
        return $this->observations->statistics();
    }

    /**
     * @return non-negative-int
     */
    public function numberOfAssertions(): int
    {
        return $this->numberOfAssertions;
    }

    /**
     * The messages with which the property is considered risky.
     *
     * @param bool                                                           $passed                  whether the property passed, that is, whether all examples that were checked passed
     * @param list<array{label: non-empty-string, minimumPercentage: float}> $requiredClassifications the classifications that #[RequireClassification] requires
     *
     * @return list<non-empty-string>
     */
    public function riskyMessages(bool $passed, array $requiredClassifications): array
    {
        $messages = [];

        // a property for which fewer examples could be generated than were
        // to be checked is only considered risky when none of them was
        // checked because all of them were rejected
        if ($this->unhealthyBecauseOf !== null) {
            $messages[] = sprintf(
                '%s because generating examples was stopped after %s while fewer than %d examples were checked; %s',
                $this->checkedForOnly(),
                $this->rejectedExamples(),
                $this->examplesCheckedUntilHealthIsAssumed,
                $this->unhealthyBecauseOf->advice(),
            );
        } elseif ($this->numberOfExamplesChecked < $this->numberOfExamples &&
            ($this->numberOfExamplesChecked + $this->numberOfExamplesRejected === $this->maximumNumberOfExamplesGenerated() ||
             ($this->exhausted && $this->numberOfExamplesChecked === 0))) {
            $messages[] = sprintf(
                '%s, %s; %s',
                $this->checkedForOnly(),
                $this->rejectedExamples(),
                $this->mostFrequentRejectionReason()->advice(),
            );
        }

        if ($this->aborted) {
            $messages[] = $this->checkedForOnly() . ' because the test run was aborted';
        }

        if ($passed && $this->numberOfExamplesChecked > 0) {
            foreach ($this->unmetClassificationRequirements($requiredClassifications) as $message) {
                $messages[] = $message;
            }
        }

        // the steps are drawn so that most examples take at least one step,
        // so a state machine that took no step in most of them is stuck
        if ($passed && $this->numberOfExamplesWhoseStateMachineTookNoStep * 2 > $this->numberOfExamplesThatRanAStateMachine) {
            $messages[] = sprintf(
                'The state machine of this property took no step in %d of %d examples because none of its rules could be applied; make sure that the preconditions of the rules hold and that values are produced for the pools from which they take values',
                $this->numberOfExamplesWhoseStateMachineTookNoStep,
                $this->numberOfExamplesThatRanAStateMachine,
            );
        }

        // the reasons that concern the property as a whole are decided like
        // those of a test, on all examples that were checked
        $checks  = new RiskyTestChecks($this->reportUselessTests);
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
     * @param ?non-negative-int $numberOfSteps the number of steps that the state machine of an example took, null when it did not run one
     */
    private function countStepsOfStateMachine(?int $numberOfSteps): void
    {
        if ($numberOfSteps === null) {
            return;
        }

        $this->numberOfExamplesThatRanAStateMachine++;

        if ($numberOfSteps === 0) {
            $this->numberOfExamplesWhoseStateMachineTookNoStep++;
        }
    }

    /**
     * Returns, when no further examples are generated because too many
     * examples were rejected before enough examples were checked, the reason
     * for which examples were rejected whose advice is given, and null
     * otherwise.
     */
    private function reasonWhyExamplesAreNoLongerGenerated(): ?RejectionReason
    {
        if ($this->numberOfExamplesChecked >= $this->examplesCheckedUntilHealthIsAssumed || $this->rejections === []) {
            return null;
        }

        if ($this->numberOfExamplesRejected >= $this->maximumRejectedExamplesUntilHealthIsAssumed) {
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

        if ($numberOfTooLargeExamples >= $this->maximumTooLargeExamplesUntilHealthIsAssumed) {
            return $mostFrequentTooLarge;
        }

        return null;
    }

    /**
     * Begins a message that says for how few of the examples that were to be
     * generated the property was checked, for instance "This property was
     * checked for only 3 of 10 examples". When explicit examples were checked
     * as well, it says that the examples are the generated ones and how many
     * explicit examples there were, for instance "This property was checked
     * for only 0 of 10 generated examples (and 1 explicit example)".
     *
     * @return non-empty-string
     */
    private function checkedForOnly(): string
    {
        if ($this->numberOfExplicitExamplesChecked === 0) {
            return sprintf(
                'This property was checked for only %d of %d examples',
                $this->numberOfExamplesChecked,
                $this->numberOfExamples,
            );
        }

        if ($this->numberOfExplicitExamplesChecked === 1) {
            $explicitExamples = '1 explicit example';
        } else {
            $explicitExamples = sprintf('%d explicit examples', $this->numberOfExplicitExamplesChecked);
        }

        return sprintf(
            'This property was checked for only %d of %d generated examples (and %s)',
            $this->numberOfExamplesChecked,
            $this->numberOfExamples,
            $explicitExamples,
        );
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
     * The messages for the labels with which fewer of the generated examples
     * that were checked were classified than #[RequireClassification]
     * requires.
     *
     * @param list<array{label: non-empty-string, minimumPercentage: float}> $requiredClassifications
     *
     * @return list<non-empty-string>
     */
    private function unmetClassificationRequirements(array $requiredClassifications): array
    {
        $messages = [];

        foreach ($requiredClassifications as $requirement) {
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
}
