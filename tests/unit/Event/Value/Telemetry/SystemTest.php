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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(System::class)]
#[Small]
final class SystemTest extends TestCase
{
    public function testSnapshotReturnsSnapshot(): void
    {
        $time = HRTime::fromSecondsAndNanoseconds(...hrtime(false));

        $clock = new class($time) implements StopWatch
        {
            private readonly HRTime $time;

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

        $memoryMeter = new class($memoryUsage, $peakMemoryUsage) implements MemoryMeter
        {
            private readonly MemoryUsage $memoryUsage;
            private readonly MemoryUsage $peakMemoryUsage;

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

        $garbageCollectorProvider = new class($garbageCollectorStatus) implements GarbageCollectorStatusProvider
        {
            private readonly GarbageCollectorStatus $status;

            public function __construct(GarbageCollectorStatus $status)
            {
                $this->status = $status;
            }

            public function status(): GarbageCollectorStatus
            {
                return $this->status;
            }
        };

        $snapshot = (new System($clock, $memoryMeter, $garbageCollectorProvider))->snapshot();

        $this->assertSame($time, $snapshot->time());
        $this->assertSame($memoryUsage, $snapshot->memoryUsage());
        $this->assertSame($peakMemoryUsage, $snapshot->peakMemoryUsage());
        $this->assertSame($garbageCollectorStatus, $snapshot->garbageCollectorStatus());
    }
}
