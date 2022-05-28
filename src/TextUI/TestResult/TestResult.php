<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\TestResult;

use function count;
use PHPUnit\Event\Test\Aborted;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\Skipped;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult
{
    private int $numberOfTests;

    /**
     * @psalm-var list<BeforeFirstTestMethodErrored|Errored>
     */
    private array $erroredTests;

    /**
     * @psalm-var list<Failed>
     */
    private array $failedTests;

    /**
     * @psalm-var array<string,list<PassedWithWarning>>
     */
    private array $testsWithWarnings;

    /**
     * @psalm-var array<string,list<ConsideredRisky>>
     */
    private array $riskyTests;

    /**
     * @psalm-var list<Skipped>
     */
    private array $skippedTests;

    /**
     * @psalm-var list<Aborted>
     */
    private array $incompleteTests;

    /**
     * @psalm-param list<BeforeFirstTestMethodErrored|Errored> $erroredTests
     * @psalm-param list<Failed> $failedTests
     * @psalm-param array<string,list<PassedWithWarning>> $testsWithWarnings
     * @psalm-param array<string,list<ConsideredRisky>> $riskyTests
     * @psalm-param list<Skipped> $skippedTests
     * @psalm-param list<Aborted> $incompleteTests
     */
    public function __construct(int $numberOfTests, array $erroredTests, array $failedTests, array $testsWithWarnings, array $riskyTests, array $skippedTests, array $incompleteTests)
    {
        $this->numberOfTests     = $numberOfTests;
        $this->erroredTests      = $erroredTests;
        $this->failedTests       = $failedTests;
        $this->testsWithWarnings = $testsWithWarnings;
        $this->riskyTests        = $riskyTests;
        $this->skippedTests      = $skippedTests;
        $this->incompleteTests   = $incompleteTests;
    }

    public function numberOfTests(): int
    {
        return $this->numberOfTests;
    }

    public function hasErroredTests(): bool
    {
        return count($this->erroredTests) > 0;
    }

    public function hasFailedTests(): bool
    {
        return count($this->failedTests) > 0;
    }

    public function hasTestsWithWarnings(): bool
    {
        return count($this->testsWithWarnings) > 0;
    }

    public function hasRiskyTests(): bool
    {
        return count($this->riskyTests) > 0;
    }

    public function hasSkippedTests(): bool
    {
        return count($this->skippedTests) > 0;
    }

    public function hasIncompleteTests(): bool
    {
        return count($this->incompleteTests) > 0;
    }

    public function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringWarnings() && empty($this->testsWithWarnings);
    }

    public function wasSuccessfulIgnoringWarnings(): bool
    {
        return empty($this->erroredTests) && empty($this->failedTests);
    }
}
