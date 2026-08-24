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

use function array_keys;
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
    private readonly TestRunHistory $testRunHistory;
    private readonly PathHasher $hasher;

    public function __construct(TestImpactDataFile $testImpactDataFile, TestRunHistory $testRunHistory, ?PathHasher $hasher = null)
    {
        $this->testImpactDataFile = $testImpactDataFile;
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
        $recording = $this->testImpactDataFile->recording();

        if ($recording === null || $recording->isEmpty()) {
            return Selection::everything('no test impact data has been recorded');
        }

        if ($changedPaths === null) {
            $change = $recording->changeNothingIsKnownAbout($this->hasher, $sourceFiles);
        } else {
            $change = $recording->pathNothingIsKnownAbout($changedPaths);
        }

        if ($change !== null) {
            return Selection::everything($change);
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
                $selected[$id] = true;

                continue;
            }

            if (!$recording->knows($id)) {
                $selected[$id] = true;

                continue;
            }

            if ($this->testRunHistory->status(TestRunHistoryId::fromReorderable($test))->isKnown()) {
                $selected[$id] = true;

                continue;
            }

            if (isset($affected[$id])) {
                $selected[$id] = true;
            }
        }

        $selected = $this->withTestsThatAreDependedUpon($tests, $selected);

        return Selection::of(
            array_keys($selected),
            sprintf(
                '%d of %d tests can be affected by what changed',
                count($selected),
                count($tests),
            ),
            count($tests),
        );
    }

    /**
     * A test that is selected and that depends on another test cannot be run
     * without the test it depends on: it would error instead of being run.
     *
     * @param list<PhptTestCase|TestCase>   $tests
     * @param array<non-empty-string, true> $selected
     *
     * @return array<non-empty-string, true>
     */
    private function withTestsThatAreDependedUpon(array $tests, array $selected): array
    {
        do {
            $added = false;

            foreach ($tests as $test) {
                if (!$test instanceof TestCase) {
                    continue;
                }

                $id = $test->valueObjectForEvents()->id();

                if (isset($selected[$id])) {
                    continue;
                }

                if (!$this->provides($test, $tests, $selected)) {
                    continue;
                }

                $selected[$id] = true;
                $added         = true;
            }
        } while ($added);

        return $selected;
    }

    /**
     * @param list<PhptTestCase|TestCase>   $tests
     * @param array<non-empty-string, true> $selected
     */
    private function provides(TestCase $test, array $tests, array $selected): bool
    {
        $provided = [];

        foreach ($test->provides() as $dependency) {
            $provided[$dependency->getTarget()] = true;
        }

        foreach ($tests as $other) {
            if (!$other instanceof TestCase) {
                continue;
            }

            if (!isset($selected[$other->valueObjectForEvents()->id()])) {
                continue;
            }

            foreach ($other->requires() as $required) {
                if (isset($provided[$required->getTarget()])) {
                    return true;
                }
            }
        }

        return false;
    }
}
