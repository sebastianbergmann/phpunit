<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetry;

use function hrtime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Snapshot::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class SnapshotTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $time                   = HRTime::fromSecondsAndNanoseconds(...hrtime(false));
        $memoryUsage            = MemoryUsage::fromBytes(2000);
        $peakMemoryUsage        = MemoryUsage::fromBytes(3000);
        $garbageCollectorStatus = new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0);
        $userCpuTime            = CpuTime::fromSecondsAndNanoseconds(1, 200);
        $systemCpuTime          = CpuTime::fromSecondsAndNanoseconds(2, 300);
        $totalCpuTime           = CpuTime::fromSecondsAndNanoseconds(3, 500);

        $snapshot = new Snapshot(
            $time,
            $memoryUsage,
            $peakMemoryUsage,
            $garbageCollectorStatus,
            $userCpuTime,
            $systemCpuTime,
            $totalCpuTime,
        );

        $this->assertSame($time, $snapshot->time());
        $this->assertSame($memoryUsage, $snapshot->memoryUsage());
        $this->assertSame($peakMemoryUsage, $snapshot->peakMemoryUsage());
        $this->assertSame($garbageCollectorStatus, $snapshot->garbageCollectorStatus());
        $this->assertSame($userCpuTime, $snapshot->userCpuTime());
        $this->assertSame($systemCpuTime, $snapshot->systemCpuTime());
        $this->assertSame($totalCpuTime, $snapshot->totalCpuTime());
    }
}
