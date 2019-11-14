<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetric;

use function memory_get_usage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Telemetric\SystemMemoryMeter
 */
final class SystemMemoryMeterTest extends TestCase
{
    public function testUsageReturnsMemoryUsage(): void
    {
        $memoryMeter = new SystemMemoryMeter();

        $usage = MemoryUsage::fromBytes(memory_get_usage(true));

        $this->assertEquals($usage, $memoryMeter->usage());
    }

    public function testPeakReturnsMemoryPeakUsage(): void
    {
        $memoryMeter = new SystemMemoryMeter();

        $peak = MemoryUsage::fromBytes(memory_get_usage(true));

        $this->assertEquals($peak, $memoryMeter->peak());
    }
}
