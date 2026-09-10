<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function array_pop;
use function assert;
use function count;
use function sprintf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;

/**
 * Which of the tests that would be run can be affected by what changed.
 *
 * Anything this does not have reliable information about is run: a test that
 * was never recorded, a test that did not pass when it was last run, a test
 * that is not a test method and therefore cannot be recorded, and every test
 * there is when a change is one that nothing that was recorded accounts for.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Selector
{
    private readonly TestImpactDataFile $testImpactDataFile;
    private readonly Provenance $provenance;
    private readonly TestRunHistory $testRunHistory;
    private readonly PathHasher $hasher;

    /**
     * @param Provenance $provenance where what this test run records comes from
     */
    public function __construct(TestImpactDataFile $testImpactDataFile, Provenance $provenance, TestRunHistory $testRunHistory, ?PathHasher $hasher = null)
    {
        $this->testImpactDataFile = $testImpactDataFile;
        $this->provenance         = $provenance;
        $this->testRunHistory     = $testRunHistory;

        if ($hasher === null) {
            $hasher = new PathHasher;
        }

        $this->hasher = $hasher;
    }

    /**
     * What changed is worked out from what was recorded, unless it is named:
     * a developer who knows what they changed, and asks version control for
     * it, knows something PHPUnit cannot work out for itself, such as which of
     * the files that differ from what was recorded are their own doing.
     *
     * @param list<PhptTestCase|TestCase> $tests        the tests that would be run
     * @param list<non-empty-string>      $sourceFiles  the files that are subject to code coverage analysis
     * @param ?list<non-empty-string>     $changedPaths the files and directories that changed, when they are named
     */
    public function select(array $tests, array $sourceFiles, ?array $changedPaths = null): Selection
    {
        $explanation = $this->explain($tests, $sourceFiles, $changedPaths);

        if ($explanation->isEverything()) {
            return Selection::everything($explanation->reasonEverythingIsRun());
        }

        return Selection::of(
            $explanation->testsThatAreRun(),
            sprintf(
                '%d of %d tests can be affected by what changed',
                $explanation->numberOfTestsThatAreRun(),
                $explanation->numberOfTestsThatWereConsidered(),
            ),
            $explanation->numberOfTestsThatWereConsidered(),
        );
    }

    /**
     * Which of the tests that would be run can be affected by what changed,
     * and why each of them is run.
     *
     * This is what the selection is made of: what is explained here is what
     * select() decides, and not a second opinion about it.
     *
     * @param list<PhptTestCase|TestCase> $tests        the tests that would be run
     * @param list<non-empty-string>      $sourceFiles  the files that are subject to code coverage analysis
     * @param ?list<non-empty-string>     $changedPaths the files and directories that changed, when they are named
     */
    public function explain(array $tests, array $sourceFiles, ?array $changedPaths = null): Explanation
    {
        $recording = $this->testImpactDataFile->recording($this->provenance);

        if ($recording === null || $recording->isEmpty()) {
            return Explanation::everything($this->reasonNothingCanBeSelectedFrom());
        }

        if ($changedPaths === null) {
            $change = $recording->changeNothingIsKnownAbout($this->hasher, $sourceFiles);
        } else {
            $change = $recording->pathNothingIsKnownAbout($changedPaths);
        }

        if ($change !== null) {
            return Explanation::everything($change);
        }

        if ($changedPaths === null) {
            $affected = $recording->testsAffectedByWhatChanged($this->hasher);
        } else {
            $affected = $recording->testsThatDependOnAnyOf($changedPaths);
        }

        $selected = [];

        foreach ($tests as $test) {
            $id = $test->valueObjectForEvents()->id();

            if (!$test instanceof TestCase) {
                /*
                 * A test that is not a test method is never recorded, so
                 * nothing is known about it and it has to be run.
                 */
                $selected[$id] = ExplainedTest::from($id, SelectionReason::ItCannotBeRecorded);

                continue;
            }

            /*
             * The repetitions of a repeated test, and the attempts of a
             * retried test, are recorded as the test they are repetitions and
             * attempts of: what such a test depends on does not depend on
             * which repetition of it was run.
             */
            $recordedId = $test->valueObjectForEvents()->idWithoutRepetitionAndAttempt();

            if (!$recording->knows($recordedId)) {
                $selected[$id] = ExplainedTest::from($id, SelectionReason::NothingIsKnownAboutIt);

                continue;
            }

            if ($this->testRunHistory->status(TestRunHistoryId::fromReorderable($test))->isKnown()) {
                $selected[$id] = ExplainedTest::from($id, SelectionReason::ItDidNotPass);

                continue;
            }

            if (isset($affected[$recordedId])) {
                $selected[$id] = ExplainedTest::from(
                    $id,
                    SelectionReason::DependsOnSomethingThatChanged,
                    $affected[$recordedId],
                );
            }
        }

        $selected = $this->withTestsThatAreDependedUpon($tests, $selected);

        return Explanation::of($selected, count($tests));
    }

    /**
     * What was recorded by a test run of the other kind is there, but it does
     * not answer what this test run asks, and saying that nothing has been
     * recorded would send the developer looking for a recording that is right
     * in front of them.
     *
     * @return non-empty-string
     */
    private function reasonNothingCanBeSelectedFrom(): string
    {
        $provenanceOfWhatIsThere = $this->testImpactDataFile->provenance();

        if ($provenanceOfWhatIsThere === null || $provenanceOfWhatIsThere === $this->provenance) {
            return 'no test impact data has been recorded';
        }

        if ($provenanceOfWhatIsThere === Provenance::CoverageTargets) {
            return 'what is known was recorded from the code coverage targets the tests declare, and this test run records what the tests execute';
        }

        return 'what is known was recorded from what the tests executed, and this test run records the code coverage targets the tests declare';
    }

    /**
     * A test that is selected and that depends on another test cannot be run
     * without the test it depends on: it would error, or be skipped, instead
     * of being run.
     *
     * What a selected test depends on may itself depend on something else,
     * which is why what is selected is followed until nothing is added.
     *
     * @param list<PhptTestCase|TestCase>            $tests
     * @param array<non-empty-string, ExplainedTest> $selected
     *
     * @return array<non-empty-string, ExplainedTest>
     */
    private function withTestsThatAreDependedUpon(array $tests, array $selected): array
    {
        $providers = [];
        $pending   = [];

        foreach ($tests as $test) {
            if (!$test instanceof TestCase) {
                continue;
            }

            foreach ($this->targetsProvidedBy($test) as $target) {
                $providers[$target][] = $test;
            }

            if (isset($selected[$test->valueObjectForEvents()->id()])) {
                $pending[] = $test;
            }
        }

        while ($pending !== []) {
            $test = array_pop($pending);

            assert($test instanceof TestCase);

            foreach ($test->requires() as $required) {
                $target = $required->getTarget();

                if (!isset($providers[$target])) {
                    continue;
                }

                foreach ($providers[$target] as $provider) {
                    $id = $provider->valueObjectForEvents()->id();

                    if (isset($selected[$id])) {
                        continue;
                    }

                    $selected[$id] = ExplainedTest::from($id, SelectionReason::AnotherTestDependsOnIt);
                    $pending[]     = $provider;
                }
            }
        }

        return $selected;
    }

    /**
     * What a test can be depended upon as.
     *
     * A test is depended upon by name, and as one of the tests of the class it
     * belongs to: a test that declares that it depends on a class depends on
     * every test of that class having passed, and is skipped when they were
     * not run.
     *
     * @return list<non-empty-string>
     */
    private function targetsProvidedBy(TestCase $test): array
    {
        $targets = [];

        foreach ($test->provides() as $dependency) {
            $target = $dependency->getTarget();

            if ($target === '') {
                continue; // @codeCoverageIgnore
            }

            $targets[] = $target;
        }

        $targets[] = $test::class . '::class';

        return $targets;
    }
}
