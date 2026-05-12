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

#[CoversClass(SystemCpuTimeMeter::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class SystemCpuTimeMeterTest extends TestCase
{
    public function testUserCpuTimeReturnsCpuTime(): void
    {
        $this->assertInstanceOf(CpuTime::class, new SystemCpuTimeMeter()->userCpuTime());
    }

    public function testSystemCpuTimeReturnsCpuTime(): void
    {
        $this->assertInstanceOf(CpuTime::class, new SystemCpuTimeMeter()->systemCpuTime());
    }

    public function testUserCpuTimeIsMonotonicallyNonDecreasing(): void
    {
        $meter = new SystemCpuTimeMeter;

        $first = $meter->userCpuTime();

        $sink = 0;

        for ($i = 0; $i < 100000; $i++) {
            $sink += $i;
        }

        $second = $meter->userCpuTime();

        $this->assertGreaterThan(0, $sink);
        $this->assertGreaterThanOrEqual($first->asFloat(), $second->asFloat());
    }
}
