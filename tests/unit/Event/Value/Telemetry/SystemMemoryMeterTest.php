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

use function memory_get_peak_usage;
use function memory_get_usage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemMemoryMeter::class)]
#[Small]
final class SystemMemoryMeterTest extends TestCase
{
    public function testMemoryUsageReturnsMemoryUsage(): void
    {
        $memoryMeter = new SystemMemoryMeter;

        $memoryUsage = MemoryUsage::fromBytes(memory_get_usage(true));

        $this->assertEquals($memoryUsage, $memoryMeter->memoryUsage());
    }

    public function testPeakMemoryUsageReturnsMemoryPeakUsage(): void
    {
        $memoryMeter = new SystemMemoryMeter;

        $peakMemoryUsage = MemoryUsage::fromBytes(memory_get_peak_usage(true));

        $this->assertEquals($peakMemoryUsage, $memoryMeter->peakMemoryUsage());
    }
}
