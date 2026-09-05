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
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordedTests::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class RecordedTestsTest extends TestCase
{
    public function testKnowsTheTestsThatDependOnTheFileAsItIsNow(): void
    {
        $this->assertSame(
            ['FooTest::testOne'],
            RecordedTests::from(['FooTest::testOne'], ['BarTest::testOne'], Provenance::ObservedExecution)->thatDependOnTheFileAsItIsNow(),
        );
    }

    public function testKnowsTheTestsThatDependOnAnEarlierVersionOfTheFile(): void
    {
        $this->assertSame(
            ['BarTest::testOne'],
            RecordedTests::from(['FooTest::testOne'], ['BarTest::testOne'], Provenance::ObservedExecution)->thatDependOnAnEarlierVersionOfTheFile(),
        );
    }

    public function testIsEmptyWhenNoTestIsRecordedForTheFile(): void
    {
        $this->assertTrue(RecordedTests::from([], [], Provenance::ObservedExecution)->isEmpty());
    }

    public function testIsNotEmptyWhenATestExecutedTheFileAsItIsNow(): void
    {
        $this->assertFalse(RecordedTests::from(['FooTest::testOne'], [], Provenance::ObservedExecution)->isEmpty());
    }

    public function testKnowsWhenItWasDerivedFromCoverageTargets(): void
    {
        $this->assertTrue(RecordedTests::from([], [], Provenance::CoverageTargets)->wereDerivedFromCoverageTargets());
        $this->assertFalse(RecordedTests::from([], [], Provenance::ObservedExecution)->wereDerivedFromCoverageTargets());
    }

    public function testIsNotEmptyWhenATestExecutedAnEarlierVersionOfTheFile(): void
    {
        $this->assertFalse(RecordedTests::from([], ['FooTest::testOne'], Provenance::ObservedExecution)->isEmpty());
    }
}
