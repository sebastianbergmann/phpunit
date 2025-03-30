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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(GarbageCollectorStatus::class)]
#[Small]
final class GarbageCollectorStatusTest extends TestCase
{
    public function testHasRuns(): void
    {
        $this->assertSame(1, $this->garbageCollectorStatus()->runs());
    }

    public function testHasCollected(): void
    {
        $this->assertSame(2, $this->garbageCollectorStatus()->collected());
    }

    public function testHasThreshold(): void
    {
        $this->assertSame(3, $this->garbageCollectorStatus()->threshold());
    }

    public function testHasRoots(): void
    {
        $this->assertSame(4, $this->garbageCollectorStatus()->roots());
    }

    public function testMayHaveRunning(): void
    {
        $this->assertTrue($this->garbageCollectorStatus()->isRunning());
    }

    public function testMayHaveApplicationTime(): void
    {
        $this->assertSame(5.0, $this->garbageCollectorStatus()->applicationTime());
    }

    public function testMayHaveCollectorTime(): void
    {
        $this->assertSame(6.0, $this->garbageCollectorStatus()->collectorTime());
    }

    public function testMayHaveDestructorTime(): void
    {
        $this->assertSame(7.0, $this->garbageCollectorStatus()->destructorTime());
    }

    public function testMayHaveFreeTime(): void
    {
        $this->assertSame(8.0, $this->garbageCollectorStatus()->freeTime());
    }

    public function testMayHaveProtected(): void
    {
        $this->assertTrue($this->garbageCollectorStatus()->isProtected());
    }

    public function testMayHaveFull(): void
    {
        $this->assertTrue($this->garbageCollectorStatus()->isFull());
    }

    public function testMayHaveBufferSize(): void
    {
        $this->assertSame(9, $this->garbageCollectorStatus()->bufferSize());
    }

    private function garbageCollectorStatus(): GarbageCollectorStatus
    {
        return new GarbageCollectorStatus(
            1,
            2,
            3,
            4,
            5.0,
            6.0,
            7.0,
            8.0,
            true,
            true,
            true,
            9,
        );
    }
}
