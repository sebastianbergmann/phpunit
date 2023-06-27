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

#[CoversClass(Snapshot::class)]
#[Small]
final class SnapshotTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $time                   = HRTime::fromSecondsAndNanoseconds(...hrtime(false));
        $memoryUsage            = MemoryUsage::fromBytes(2000);
        $peakMemoryUsage        = MemoryUsage::fromBytes(3000);
        $garbageCollectorStatus = new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0);

        $snapshot = new Snapshot(
            $time,
            $memoryUsage,
            $peakMemoryUsage,
            $garbageCollectorStatus,
        );

        $this->assertSame($time, $snapshot->time());
        $this->assertSame($memoryUsage, $snapshot->memoryUsage());
        $this->assertSame($peakMemoryUsage, $snapshot->peakMemoryUsage());
        $this->assertSame($garbageCollectorStatus, $snapshot->garbageCollectorStatus());
    }
}
