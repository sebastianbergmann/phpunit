<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RiskyTestChecks::class)]
#[Small]
final class RiskyTestChecksTest extends TestCase
{
    public function testATestThatPerformedNoAssertionsIsRiskyWhenUselessTestsAreReported(): void
    {
        $this->assertSame('This test did not perform any assertions', new RiskyTestChecks(true)->performedNoAssertions(0, false));
    }

    public function testATestThatPerformedNoAssertionsIsNotRiskyWhenUselessTestsAreNotReported(): void
    {
        $this->assertNull(new RiskyTestChecks(false)->performedNoAssertions(0, false));
    }

    public function testATestThatPerformedAssertionsIsNotRiskyForPerformingNone(): void
    {
        $this->assertNull(new RiskyTestChecks(true)->performedNoAssertions(1, false));
    }

    public function testATestThatIsNotExpectedToPerformAssertionsIsNotRiskyForPerformingNone(): void
    {
        $this->assertNull(new RiskyTestChecks(true)->performedNoAssertions(0, true));
    }

    public function testATestThatIsNotExpectedToPerformAssertionsIsRiskyWhenItPerformedOne(): void
    {
        $this->assertSame(
            'This test is not expected to perform assertions but performed 1 assertion',
            new RiskyTestChecks(true)->performedUnexpectedAssertions(1, true),
        );
    }

    public function testATestThatIsNotExpectedToPerformAssertionsIsRiskyWhenItPerformedSome(): void
    {
        $this->assertSame(
            'This test is not expected to perform assertions but performed 2 assertions',
            new RiskyTestChecks(true)->performedUnexpectedAssertions(2, true),
        );
    }

    public function testATestThatIsNotExpectedToPerformAssertionsIsNotRiskyWhenItPerformedNone(): void
    {
        $this->assertNull(new RiskyTestChecks(true)->performedUnexpectedAssertions(0, true));
    }

    public function testATestThatMayPerformAssertionsIsNotRiskyForPerformingThem(): void
    {
        $this->assertNull(new RiskyTestChecks(true)->performedUnexpectedAssertions(2, false));
    }

    public function testATestThatDidNotContributeToCodeCoverageIsRisky(): void
    {
        $this->assertSame('This test does not contribute to code coverage', new RiskyTestChecks(true)->didNotContributeToCodeCoverage(false));
    }

    public function testATestThatContributedToCodeCoverageIsNotRiskyForThat(): void
    {
        $this->assertNull(new RiskyTestChecks(true)->didNotContributeToCodeCoverage(true));
    }

    public function testATestForWhichContributionToCodeCoverageWasNotCheckedIsNotRiskyForThat(): void
    {
        $this->assertNull(new RiskyTestChecks(true)->didNotContributeToCodeCoverage(null));
    }
}
