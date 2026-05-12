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

#[CoversClass(System::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class SystemTest extends TestCase
{
    public function testSnapshotReturnsSnapshot(): void
    {
        $time = HRTime::fromSecondsAndNanoseconds(...hrtime(false));

        $clock = new readonly class($time) implements StopWatch
        {
            private HRTime $time;

            public function __construct(HRTime $time)
            {
                $this->time = $time;
            }

            public function current(): HRTime
            {
                return $this->time;
            }
        };

        $memoryUsage     = MemoryUsage::fromBytes(2000);
        $peakMemoryUsage = MemoryUsage::fromBytes(3000);

        $memoryMeter = new readonly class($memoryUsage, $peakMemoryUsage) implements MemoryMeter
        {
            private MemoryUsage $memoryUsage;
            private MemoryUsage $peakMemoryUsage;

            public function __construct(MemoryUsage $memoryUsage, MemoryUsage $peakMemoryUsage)
            {
                $this->memoryUsage     = $memoryUsage;
                $this->peakMemoryUsage = $peakMemoryUsage;
            }

            public function memoryUsage(): MemoryUsage
            {
                return $this->memoryUsage;
            }

            public function peakMemoryUsage(): MemoryUsage
            {
                return $this->peakMemoryUsage;
            }
        };

        $garbageCollectorStatus = new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0);

        $garbageCollectorProvider = new readonly class($garbageCollectorStatus) implements GarbageCollectorStatusProvider
        {
            private GarbageCollectorStatus $status;

            public function __construct(GarbageCollectorStatus $status)
            {
                $this->status = $status;
            }

            public function status(): GarbageCollectorStatus
            {
                return $this->status;
            }
        };

        $userCpuTime   = CpuTime::fromSecondsAndNanoseconds(1, 200);
        $systemCpuTime = CpuTime::fromSecondsAndNanoseconds(2, 300);

        $cpuTimeMeter = new readonly class($userCpuTime, $systemCpuTime) implements CpuTimeMeter
        {
            private CpuTime $userCpuTime;
            private CpuTime $systemCpuTime;

            public function __construct(CpuTime $userCpuTime, CpuTime $systemCpuTime)
            {
                $this->userCpuTime   = $userCpuTime;
                $this->systemCpuTime = $systemCpuTime;
            }

            public function userCpuTime(): CpuTime
            {
                return $this->userCpuTime;
            }

            public function systemCpuTime(): CpuTime
            {
                return $this->systemCpuTime;
            }
        };

        $snapshot = new System($clock, $memoryMeter, $garbageCollectorProvider, $cpuTimeMeter)->snapshot();

        $this->assertSame($time, $snapshot->time());
        $this->assertSame($memoryUsage, $snapshot->memoryUsage());
        $this->assertSame($peakMemoryUsage, $snapshot->peakMemoryUsage());
        $this->assertSame($garbageCollectorStatus, $snapshot->garbageCollectorStatus());
        $this->assertSame($userCpuTime, $snapshot->userCpuTime());
        $this->assertSame($systemCpuTime, $snapshot->systemCpuTime());
        $this->assertSame(3, $snapshot->totalCpuTime()->seconds());
        $this->assertSame(500, $snapshot->totalCpuTime()->nanoseconds());
    }
}
