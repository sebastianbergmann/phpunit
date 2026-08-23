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
final readonly class ListTestsThatExecutedCommand implements Command
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
                    'Source file "%s" does not exist' . PHP_EOL,
                    $this->file,
                ),
                Result::FAILURE,
            );
        }

        $tests = $this->testImpactDataFile->testsThatExecuted($file);

        /*
         * What was recorded from the code coverage targets a test declares is
         * not an observation, and must not be reported as one.
         */
        $what = 'that executed';

        if ($tests->wereDerivedFromCoverageTargets()) {
            $what = 'whose code coverage targets name';
        }

        if ($tests->isEmpty()) {
            return Result::from(
                sprintf(
                    'No test %s %s is recorded' . PHP_EOL,
                    $what,
                    $file,
                ),
            );
        }

        return Result::from(
            $this->listOf(
                sprintf('Tests %s %s as it is now:', $what, $file),
                $tests->thatExecutedTheFileAsItIsNow(),
            ) .
            $this->listOf(
                sprintf('Tests %s an earlier version of %s:', $what, $file),
                $tests->thatExecutedAnEarlierVersionOfTheFile(),
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
