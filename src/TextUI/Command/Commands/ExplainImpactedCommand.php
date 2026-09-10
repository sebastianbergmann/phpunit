<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use function count;
use function sprintf;
use PHPUnit\Runner\TestImpactAnalysis\Explanation;
use PHPUnit\Runner\TestImpactAnalysis\Provenance;
use PHPUnit\Runner\TestImpactAnalysis\RecordingTime;
use PHPUnit\Runner\TestImpactAnalysis\SelectionReason;

/**
 * Reports which tests can be affected by what changed, and why each of them
 * can be.
 *
 * A test is run for one of a handful of reasons, and only one of them is that
 * the test depends on something that changed. A developer who sees more tests
 * run than they expected is not helped by the number alone: what they need is
 * which of their tests are run for a reason that has nothing to do with what
 * they changed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExplainImpactedCommand implements Command
{
    /**
     * The reasons in the order they are reported: what changed is what was
     * asked about, and everything else is there to explain the difference
     * between that and what is actually run.
     */
    private const array ORDER = [
        SelectionReason::DependsOnSomethingThatChanged,
        SelectionReason::NothingIsKnownAboutIt,
        SelectionReason::ItDidNotPass,
        SelectionReason::AnotherTestDependsOnIt,
        SelectionReason::ItCannotBeRecorded,
    ];
    private Explanation $explanation;
    private Provenance $provenance;

    public function __construct(Explanation $explanation, Provenance $provenance)
    {
        $this->explanation = $explanation;
        $this->provenance  = $provenance;
    }

    public function execute(): Result
    {
        $buffer     = '';
        $recordedAt = $this->explanation->recordedAt();

        /*
         * Where what is reported comes from, and when it was recorded, is only
         * said when something was recorded: a run that falls back to running
         * every test because nothing was recorded says why it does, and saying
         * what was recorded from would claim that something was recorded at
         * all.
         */
        if ($recordedAt !== null) {
            $buffer = $this->provenanceOfWhatIsReported($recordedAt) . PHP_EOL . PHP_EOL;
        }

        if ($this->explanation->isEverything()) {
            return Result::from(
                $buffer .
                sprintf(
                    'Every test is run: %s' . PHP_EOL,
                    $this->explanation->reasonEverythingIsRun(),
                ),
            );
        }

        $buffer .= sprintf(
            '%d of %d tests can be affected by what changed',
            $this->explanation->numberOfTestsThatAreRun(),
            $this->explanation->numberOfTestsThatWereConsidered(),
        );

        if ($this->explanation->numberOfTestsThatAreRun() === 0) {
            return Result::from($buffer . PHP_EOL);
        }

        $buffer .= '.' . PHP_EOL . PHP_EOL;

        foreach (self::ORDER as $reason) {
            $buffer .= $this->section($reason);
        }

        return Result::from($buffer);
    }

    private function section(SelectionReason $reason): string
    {
        $tests = $this->explanation->testsRunBecause($reason);

        if ($tests === []) {
            return '';
        }

        $buffer = $this->headingFor($reason, count($tests)) . PHP_EOL;

        foreach ($tests as $test) {
            $buffer .= ' - ' . $test->test() . PHP_EOL;

            if ($test->hasFile()) {
                $buffer .= '   ' . $test->file() . PHP_EOL;
            }
        }

        return $buffer . PHP_EOL;
    }

    private function headingFor(SelectionReason $reason, int $numberOfTests): string
    {
        if ($numberOfTests === 1) {
            return '1 test ' . $this->whatOneTestDid($reason) . ':';
        }

        return sprintf(
            '%d tests %s:',
            $numberOfTests,
            $this->whatSeveralTestsDid($reason),
        );
    }

    private function whatOneTestDid(SelectionReason $reason): string
    {
        return match ($reason) {
            SelectionReason::DependsOnSomethingThatChanged => 'depends on something that changed',
            SelectionReason::NothingIsKnownAboutIt         => 'has never been recorded',
            SelectionReason::ItDidNotPass                  => 'did not pass when it was last run',
            SelectionReason::AnotherTestDependsOnIt        => 'is depended upon by another test that is run',
            SelectionReason::ItCannotBeRecorded            => 'is not a test method and can never be recorded',
        };
    }

    private function whatSeveralTestsDid(SelectionReason $reason): string
    {
        return match ($reason) {
            SelectionReason::DependsOnSomethingThatChanged => 'depend on something that changed',
            SelectionReason::NothingIsKnownAboutIt         => 'have never been recorded',
            SelectionReason::ItDidNotPass                  => 'did not pass when they were last run',
            SelectionReason::AnotherTestDependsOnIt        => 'are depended upon by another test that is run',
            SelectionReason::ItCannotBeRecorded            => 'are not test methods and can never be recorded',
        };
    }

    /**
     * What a test depends on is not always something it was observed to
     * execute: it can have been worked out from the code coverage targets the
     * test declares. Where what is reported comes from is therefore said once,
     * together with when it was recorded, instead of being claimed again in
     * each heading.
     */
    private function provenanceOfWhatIsReported(RecordingTime $recordedAt): string
    {
        if ($this->provenance === Provenance::CoverageTargets) {
            return sprintf(
                'Recorded at %s from the code coverage targets the tests declare.',
                $recordedAt->asString(),
            );
        }

        return sprintf(
            'Recorded at %s from what the tests executed.',
            $recordedAt->asString(),
        );
    }
}
