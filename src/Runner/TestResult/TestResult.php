<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use function count;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\Skipped;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult
{
    private int $numberOfTests;
    private int $numberOfTestsRun;
    private int $numberOfAssertions;

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
     * @psalm-var list<MarkedIncomplete>
     */
    private array $incompleteTests;

    /**
     * @psalm-param list<BeforeFirstTestMethodErrored|Errored> $erroredTests
     * @psalm-param list<Failed> $failedTests
     * @psalm-param array<string,list<PassedWithWarning>> $testsWithWarnings
     * @psalm-param array<string,list<ConsideredRisky>> $riskyTests
     * @psalm-param list<Skipped> $skippedTests
     * @psalm-param list<MarkedIncomplete> $incompleteTests
     */
    public function __construct(int $numberOfTests, int $numberOfTestsRun, int $numberOfAssertions, array $erroredTests, array $failedTests, array $testsWithWarnings, array $riskyTests, array $skippedTests, array $incompleteTests)
    {
        $this->numberOfTests      = $numberOfTests;
        $this->numberOfTestsRun   = $numberOfTestsRun;
        $this->numberOfAssertions = $numberOfAssertions;
        $this->erroredTests       = $erroredTests;
        $this->failedTests        = $failedTests;
        $this->testsWithWarnings  = $testsWithWarnings;
        $this->riskyTests         = $riskyTests;
        $this->skippedTests       = $skippedTests;
        $this->incompleteTests    = $incompleteTests;
    }

    public function numberOfTests(): int
    {
        return $this->numberOfTests;
    }

    public function numberOfTestsRun(): int
    {
        return $this->numberOfTestsRun;
    }

    public function numberOfAssertions(): int
    {
        return $this->numberOfAssertions;
    }

    public function numberOfErroredTests(): int
    {
        return count($this->erroredTests);
    }

    public function hasErroredTests(): bool
    {
        return $this->numberOfErroredTests() > 0;
    }

    public function numberOfFailedTests(): int
    {
        return count($this->failedTests);
    }

    public function hasFailedTests(): bool
    {
        return $this->numberOfFailedTests() > 0;
    }

    public function numberOfTestsWithWarnings(): int
    {
        return count($this->testsWithWarnings);
    }

    public function hasTestsWithWarnings(): bool
    {
        return $this->numberOfTestsWithWarnings() > 0;
    }

    public function numberOfRiskyTests(): int
    {
        return count($this->riskyTests);
    }

    public function hasRiskyTests(): bool
    {
        return $this->numberOfRiskyTests() > 0;
    }

    public function numberOfSkippedTests(): int
    {
        return count($this->skippedTests);
    }

    public function hasSkippedTests(): bool
    {
        return $this->numberOfSkippedTests() > 0;
    }

    public function numberOfIncompleteTests(): int
    {
        return count($this->incompleteTests);
    }

    public function hasIncompleteTests(): bool
    {
        return $this->numberOfIncompleteTests() > 0;
    }

    public function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringWarnings() && !$this->hasTestsWithWarnings();
    }

    public function wasSuccessfulIgnoringWarnings(): bool
    {
        return !$this->hasErroredTests() && !$this->hasFailedTests();
    }

    public function wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete(): bool
    {
        return $this->wasSuccessful() && !$this->hasRiskyTests() && !$this->hasIncompleteTests() && !$this->hasSkippedTests();
    }
}
