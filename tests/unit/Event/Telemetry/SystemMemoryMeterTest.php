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

use function memory_get_usage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Telemetry\SystemMemoryMeter
 */
final class SystemMemoryMeterTest extends TestCase
{
    public function testMemoryUsageReturnsMemoryUsage(): void
    {
        $memoryMeter = new SystemMemoryMeter();

        $memoryYsage = MemoryUsage::fromBytes(memory_get_usage(true));

        $this->assertEquals($memoryYsage, $memoryMeter->memoryUsage());
    }

    public function testPeakMemoryUsageReturnsMemoryPeakUsage(): void
    {
        $memoryMeter = new SystemMemoryMeter();

        $peakMemoryUsage = MemoryUsage::fromBytes(memory_get_usage(true));

        $this->assertEquals($peakMemoryUsage, $memoryMeter->peakMemoryUsage());
    }
}
