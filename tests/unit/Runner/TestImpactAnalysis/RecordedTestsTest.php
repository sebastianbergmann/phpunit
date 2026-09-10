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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordedTests::class)]
#[UsesClass(RecordingTime::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class RecordedTestsTest extends TestCase
{
    public function testKnowsTheTestsThatDependOnTheFileAsItIsNow(): void
    {
        $this->assertSame(
            ['FooTest::testOne'],
            RecordedTests::from(['FooTest::testOne'], ['BarTest::testOne'], Provenance::ObservedExecution, null)->thatDependOnTheFileAsItIsNow(),
        );
    }

    public function testKnowsTheTestsThatDependOnAnEarlierVersionOfTheFile(): void
    {
        $this->assertSame(
            ['BarTest::testOne'],
            RecordedTests::from(['FooTest::testOne'], ['BarTest::testOne'], Provenance::ObservedExecution, null)->thatDependOnAnEarlierVersionOfTheFile(),
        );
    }

    public function testIsEmptyWhenNoTestIsRecordedForTheFile(): void
    {
        $this->assertTrue(RecordedTests::from([], [], Provenance::ObservedExecution, null)->isEmpty());
    }

    public function testIsNotEmptyWhenATestExecutedTheFileAsItIsNow(): void
    {
        $this->assertFalse(RecordedTests::from(['FooTest::testOne'], [], Provenance::ObservedExecution, null)->isEmpty());
    }

    public function testKnowsWhenItWasDerivedFromCoverageTargets(): void
    {
        $this->assertTrue(RecordedTests::from([], [], Provenance::CoverageTargets, null)->wereDerivedFromCoverageTargets());
        $this->assertFalse(RecordedTests::from([], [], Provenance::ObservedExecution, null)->wereDerivedFromCoverageTargets());
    }

    public function testIsNotEmptyWhenATestExecutedAnEarlierVersionOfTheFile(): void
    {
        $this->assertFalse(RecordedTests::from([], ['FooTest::testOne'], Provenance::ObservedExecution, null)->isEmpty());
    }

    public function testKnowsWhenTheTestsWereRecorded(): void
    {
        $recordedAt = RecordingTime::fromUnixTimestamp(1700000000);

        $this->assertSame($recordedAt, RecordedTests::from([], [], Provenance::ObservedExecution, $recordedAt)->recordedAt());
        $this->assertNull(RecordedTests::from([], [], Provenance::ObservedExecution, null)->recordedAt());
    }
}
