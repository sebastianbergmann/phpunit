<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

use DateTimeImmutable;

/**
 * The second part of the receipt of a test run: how the test run ended.
 *
 * It is the last thing that is written for a test run, so that its presence
 * shows that the test run reached its end.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Outcome
{
    private DateTimeImmutable $endTime;
    private Status $status;
    private int $shellExitCode;
    private int $numberOfTestsRun;
    private int $numberOfAssertions;
    private int $numberOfErroredTests;
    private int $numberOfFailedTests;
    private int $numberOfSkippedTests;
    private int $numberOfIncompleteTests;
    private int $numberOfRiskyTests;
    private IssueCounts $issuesTriggeredByTests;
    private IssueCounts $issuesTriggeredByTestRunner;

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(DateTimeImmutable $endTime, Status $status, int $shellExitCode, int $numberOfTestsRun, int $numberOfAssertions, int $numberOfErroredTests, int $numberOfFailedTests, int $numberOfSkippedTests, int $numberOfIncompleteTests, int $numberOfRiskyTests, IssueCounts $issuesTriggeredByTests, IssueCounts $issuesTriggeredByTestRunner)
    {
        $this->endTime                     = $endTime;
        $this->status                      = $status;
        $this->shellExitCode               = $shellExitCode;
        $this->numberOfTestsRun            = $numberOfTestsRun;
        $this->numberOfAssertions          = $numberOfAssertions;
        $this->numberOfErroredTests        = $numberOfErroredTests;
        $this->numberOfFailedTests         = $numberOfFailedTests;
        $this->numberOfSkippedTests        = $numberOfSkippedTests;
        $this->numberOfIncompleteTests     = $numberOfIncompleteTests;
        $this->numberOfRiskyTests          = $numberOfRiskyTests;
        $this->issuesTriggeredByTests      = $issuesTriggeredByTests;
        $this->issuesTriggeredByTestRunner = $issuesTriggeredByTestRunner;
    }

    public function endTime(): DateTimeImmutable
    {
        return $this->endTime;
    }

    public function status(): Status
    {
        return $this->status;
    }

    public function shellExitCode(): int
    {
        return $this->shellExitCode;
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
        return $this->numberOfErroredTests;
    }

    public function numberOfFailedTests(): int
    {
        return $this->numberOfFailedTests;
    }

    public function numberOfSkippedTests(): int
    {
        return $this->numberOfSkippedTests;
    }

    public function numberOfIncompleteTests(): int
    {
        return $this->numberOfIncompleteTests;
    }

    public function numberOfRiskyTests(): int
    {
        return $this->numberOfRiskyTests;
    }

    public function issuesTriggeredByTests(): IssueCounts
    {
        return $this->issuesTriggeredByTests;
    }

    /**
     * The issues that the test runner triggered itself as well as the issues
     * that were triggered while no test was running, for instance in a
     * bootstrap script or in a data provider.
     */
    public function issuesTriggeredByTestRunner(): IssueCounts
    {
        return $this->issuesTriggeredByTestRunner;
    }
}
