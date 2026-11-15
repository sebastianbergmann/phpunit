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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Outcome::class)]
#[UsesClass(IssueCounts::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/receipt')]
final class OutcomeTest extends TestCase
{
    public function testHasEndTime(): void
    {
        $endTime = new DateTimeImmutable;

        $this->assertSame($endTime, $this->outcome($endTime)->endTime());
    }

    public function testHasStatus(): void
    {
        $this->assertSame(Status::Stopped, $this->outcome()->status());
    }

    public function testHasShellExitCode(): void
    {
        $this->assertSame(1, $this->outcome()->shellExitCode());
    }

    public function testHasNumberOfTestsRun(): void
    {
        $this->assertSame(2, $this->outcome()->numberOfTestsRun());
    }

    public function testHasNumberOfAssertions(): void
    {
        $this->assertSame(3, $this->outcome()->numberOfAssertions());
    }

    public function testHasNumberOfErroredTests(): void
    {
        $this->assertSame(4, $this->outcome()->numberOfErroredTests());
    }

    public function testHasNumberOfFailedTests(): void
    {
        $this->assertSame(5, $this->outcome()->numberOfFailedTests());
    }

    public function testHasNumberOfSkippedTests(): void
    {
        $this->assertSame(6, $this->outcome()->numberOfSkippedTests());
    }

    public function testHasNumberOfIncompleteTests(): void
    {
        $this->assertSame(7, $this->outcome()->numberOfIncompleteTests());
    }

    public function testHasNumberOfRiskyTests(): void
    {
        $this->assertSame(8, $this->outcome()->numberOfRiskyTests());
    }

    public function testHasNumbersOfIssuesTriggeredByTests(): void
    {
        $issueCounts = new IssueCounts(1, 2, 3, 4, 5, 6, 7, 8);

        $outcome = new Outcome(new DateTimeImmutable, Status::Completed, 0, 0, 0, 0, 0, 0, 0, 0, $issueCounts, new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0));

        $this->assertSame($issueCounts, $outcome->issuesTriggeredByTests());
    }

    public function testHasNumbersOfIssuesTriggeredByTestRunner(): void
    {
        $issueCounts = new IssueCounts(1, 2, 3, 4, 5, 6, 7, 8);

        $outcome = new Outcome(new DateTimeImmutable, Status::Completed, 0, 0, 0, 0, 0, 0, 0, 0, new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0), $issueCounts);

        $this->assertSame($issueCounts, $outcome->issuesTriggeredByTestRunner());
    }

    private function outcome(DateTimeImmutable $endTime = new DateTimeImmutable): Outcome
    {
        return new Outcome(
            $endTime,
            Status::Stopped,
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
            new IssueCounts(0, 0, 0, 0, 0, 0, 0, 0),
        );
    }
}
