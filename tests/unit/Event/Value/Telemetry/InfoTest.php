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

use const PHP_VERSION;
use function version_compare;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Info::class)]
#[Small]
final class InfoTest extends TestCase
{
    public function testHasTime(): void
    {
        $this->assertInstanceOf(HRTime::class, $this->info()->time());
    }

    public function testHasMemoryUsage(): void
    {
        $this->assertInstanceOf(MemoryUsage::class, $this->info()->memoryUsage());
    }

    public function testHasPeakMemoryUsage(): void
    {
        $this->assertInstanceOf(MemoryUsage::class, $this->info()->peakMemoryUsage());
    }

    public function testHasDurationSinceStart(): void
    {
        $this->assertSame(0, $this->info()->durationSinceStart()->nanoseconds());
    }

    public function testHasDurationSincePrevious(): void
    {
        $this->assertSame(0, $this->info()->durationSincePrevious()->nanoseconds());
    }

    public function testHasMemoryUsageSinceStart(): void
    {
        $this->assertSame(0, $this->info()->memoryUsageSinceStart()->bytes());
    }

    public function testHasMemoryUsageSincePrevious(): void
    {
        $this->assertSame(0, $this->info()->memoryUsageSincePrevious()->bytes());
    }

    public function testHasGarbageCollectorStatus(): void
    {
        $this->assertInstanceOf(GarbageCollectorStatus::class, $this->info()->garbageCollectorStatus());
    }

    public function testCanBeFormattedAsString(): void
    {
        $this->assertStringMatchesFormat(
            '[00:00:00.000000000 / 00:00:00.000000000] [%d bytes]',
            $this->info()->asString(),
        );
    }

    private function info(): Info
    {
        $current = $this->telemetrySystem()->snapshot();

        return new Info(
            $current,
            $current->time()->duration($current->time()),
            $current->memoryUsage()->diff($current->memoryUsage()),
            $current->time()->duration($current->time()),
            $current->memoryUsage()->diff($current->memoryUsage()),
        );
    }

    private function telemetrySystem(): System
    {
        if (version_compare('8.3.0', PHP_VERSION, '>')) {
            $garbageCollectorStatusProvider = new Php81GarbageCollectorStatusProvider;
        } else {
            $garbageCollectorStatusProvider = new Php83GarbageCollectorStatusProvider;
        }

        return new System(
            new SystemStopWatch,
            new SystemMemoryMeter,
            $garbageCollectorStatusProvider,
        );
    }
}
