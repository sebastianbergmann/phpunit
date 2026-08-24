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
use function realpath;
use function sprintf;
use PHPUnit\Runner\TestImpactAnalysis\TestImpactDataFile;

/**
 * Reports which tests are recorded as having executed a source file.
 *
 * This answers what was recorded, and not which tests a change to the source
 * file affects: a test that has never been run is not recorded, and a test that
 * reaches the source file in a way that executing it does not show is not
 * recorded either.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListTestsThatDependOnCommand implements Command
{
    private TestImpactDataFile $testImpactDataFile;
    private string $file;

    public function __construct(TestImpactDataFile $testImpactDataFile, string $file)
    {
        $this->testImpactDataFile = $testImpactDataFile;
        $this->file               = $file;
    }

    public function execute(): Result
    {
        $file = realpath($this->file);

        if ($file === false) {
            return Result::from(
                sprintf(
                    '"%s" does not exist' . PHP_EOL,
                    $this->file,
                ),
                Result::FAILURE,
            );
        }

        $tests = $this->testImpactDataFile->testsThatDependOn($file);

        if ($tests->isEmpty()) {
            return Result::from(
                sprintf(
                    'No test that depends on %s is recorded' . PHP_EOL,
                    $file,
                ),
            );
        }

        /*
         * What a test depends on is not always something it was observed to
         * execute: it can have been worked out from the code coverage targets
         * the test declares, and a fixture is never executed at all. Where
         * what is reported comes from is therefore said once, instead of
         * being claimed again in each heading.
         */
        $provenance = 'Recorded from what the tests executed.';

        if ($tests->wereDerivedFromCoverageTargets()) {
            $provenance = 'Recorded from the code coverage targets the tests declare.';
        }

        return Result::from(
            $provenance . PHP_EOL . PHP_EOL .
            $this->listOf(
                sprintf('Tests that depend on %s as it is now:', $file),
                $tests->thatDependOnTheFileAsItIsNow(),
            ) .
            $this->listOf(
                sprintf('Tests that depend on an earlier version of %s:', $file),
                $tests->thatDependOnAnEarlierVersionOfTheFile(),
            ),
        );
    }

    /**
     * @param non-empty-string       $heading
     * @param list<non-empty-string> $tests
     */
    private function listOf(string $heading, array $tests): string
    {
        if ($tests === []) {
            return '';
        }

        $buffer = $heading . PHP_EOL;

        foreach ($tests as $test) {
            $buffer .= ' - ' . $test . PHP_EOL;
        }

        return $buffer . PHP_EOL;
    }
}
