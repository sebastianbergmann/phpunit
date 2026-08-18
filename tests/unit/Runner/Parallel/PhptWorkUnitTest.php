<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function sys_get_temp_dir;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;

#[CoversClass(PhptWorkUnit::class)]
#[UsesClass(PhptTestCase::class)]
#[Small]
final class PhptWorkUnitTest extends TestCase
{
    public function testHasIndex(): void
    {
        $this->assertSame(5, $this->unit()->index());
    }

    public function testHasFile(): void
    {
        $this->assertSame('/path/to/test.phpt', $this->unit()->file());
    }

    public function testIsNamedAfterItsFile(): void
    {
        $this->assertSame('/path/to/test.phpt', $this->unit()->name());
    }

    public function testHasNoConflictsByDefault(): void
    {
        $this->assertSame([], $this->unit()->conflicts());
    }

    public function testHasConflicts(): void
    {
        $unit = new PhptWorkUnit(5, '/path/to/test.phpt', ['all']);

        $this->assertSame(['all'], $unit->conflicts());
    }

    public function testIsRunOnceAndAttemptedOnceByDefault(): void
    {
        $this->assertSame(1, $this->unit()->numberOfRuns());
        $this->assertSame(1, $this->unit()->maxAttempts());
    }

    public function testHasTheNumberOfRunsOfARepeatedTest(): void
    {
        $unit = new PhptWorkUnit(5, '/path/to/test.phpt', [], 3);

        $this->assertSame(3, $unit->numberOfRuns());
        $this->assertSame(1, $unit->maxAttempts());
    }

    public function testHasTheMaximumNumberOfAttemptsOfARetriedTest(): void
    {
        $unit = new PhptWorkUnit(5, '/path/to/test.phpt', [], 1, 2);

        $this->assertSame(1, $unit->numberOfRuns());
        $this->assertSame(2, $unit->maxAttempts());
    }

    public function testHasTheDurationRecordedForItsTest(): void
    {
        $testRunHistory = $this->testRunHistory();

        $testRunHistory->setTime(TestRunHistoryId::fromReorderable(new PhptTestCase('/path/to/test.phpt')), 2.5);

        $this->assertSame(2.5, $this->unit()->duration($testRunHistory));
    }

    public function testHasNoDurationWhenItsTestHasNotRunBefore(): void
    {
        $this->assertSame(0.0, $this->unit()->duration($this->testRunHistory()));
    }

    private function unit(): PhptWorkUnit
    {
        return new PhptWorkUnit(5, '/path/to/test.phpt');
    }

    /**
     * A test run history that is never loaded from or persisted to its file:
     * the tests only use the times set on the instance.
     */
    private function testRunHistory(): DefaultTestRunHistory
    {
        return new DefaultTestRunHistory(sys_get_temp_dir() . '/phpunit-phpt-work-unit-test.result.cache');
    }
}
