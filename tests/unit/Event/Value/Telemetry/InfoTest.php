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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Info::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
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

    public function testHasUserCpuTime(): void
    {
        $this->assertInstanceOf(CpuTime::class, $this->info()->userCpuTime());
    }

    public function testHasSystemCpuTime(): void
    {
        $this->assertInstanceOf(CpuTime::class, $this->info()->systemCpuTime());
    }

    public function testHasTotalCpuTime(): void
    {
        $this->assertInstanceOf(CpuTime::class, $this->info()->totalCpuTime());
    }

    public function testHasUserCpuTimeSinceStart(): void
    {
        $this->assertSame(0, $this->info()->userCpuTimeSinceStart()->nanoseconds());
        $this->assertSame(0, $this->info()->userCpuTimeSinceStart()->seconds());
    }

    public function testHasSystemCpuTimeSinceStart(): void
    {
        $this->assertSame(0, $this->info()->systemCpuTimeSinceStart()->nanoseconds());
        $this->assertSame(0, $this->info()->systemCpuTimeSinceStart()->seconds());
    }

    public function testHasTotalCpuTimeSinceStart(): void
    {
        $this->assertSame(0, $this->info()->totalCpuTimeSinceStart()->nanoseconds());
        $this->assertSame(0, $this->info()->totalCpuTimeSinceStart()->seconds());
    }

    public function testHasUserCpuTimeSincePrevious(): void
    {
        $this->assertSame(0, $this->info()->userCpuTimeSincePrevious()->nanoseconds());
        $this->assertSame(0, $this->info()->userCpuTimeSincePrevious()->seconds());
    }

    public function testHasSystemCpuTimeSincePrevious(): void
    {
        $this->assertSame(0, $this->info()->systemCpuTimeSincePrevious()->nanoseconds());
        $this->assertSame(0, $this->info()->systemCpuTimeSincePrevious()->seconds());
    }

    public function testHasTotalCpuTimeSincePrevious(): void
    {
        $this->assertSame(0, $this->info()->totalCpuTimeSincePrevious()->nanoseconds());
        $this->assertSame(0, $this->info()->totalCpuTimeSincePrevious()->seconds());
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
            $current->userCpuTime()->diff($current->userCpuTime()),
            $current->systemCpuTime()->diff($current->systemCpuTime()),
            $current->totalCpuTime()->diff($current->totalCpuTime()),
            $current->userCpuTime()->diff($current->userCpuTime()),
            $current->systemCpuTime()->diff($current->systemCpuTime()),
            $current->totalCpuTime()->diff($current->totalCpuTime()),
        );
    }

    private function telemetrySystem(): System
    {
        return new System(
            new SystemStopWatch,
            new SystemMemoryMeter,
            new SystemGarbageCollectorStatusProvider,
            new SystemCpuTimeMeter,
        );
    }
}
