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

use PHPUnit\Event\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(GarbageCollectorStatus::class)]
#[Small]
final class GarbageCollectorStatusTest extends TestCase
{
    public function testHasRuns(): void
    {
        $this->assertSame(1, $this->withoutDetails()->runs());
    }

    public function testHasCollected(): void
    {
        $this->assertSame(2, $this->withoutDetails()->collected());
    }

    public function testHasThreshold(): void
    {
        $this->assertSame(3, $this->withoutDetails()->threshold());
    }

    public function testHasRoots(): void
    {
        $this->assertSame(4, $this->withoutDetails()->roots());
    }

    public function testMayHaveExtendedInformation(): void
    {
        $this->assertTrue($this->withDetails()->hasExtendedInformation());
    }

    public function testMayHaveRunning(): void
    {
        $this->assertTrue($this->withDetails()->isRunning());
    }

    public function testMayHaveApplicationTime(): void
    {
        $this->assertSame(5.0, $this->withDetails()->applicationTime());
    }

    public function testMayHaveCollectorTime(): void
    {
        $this->assertSame(6.0, $this->withDetails()->collectorTime());
    }

    public function testMayHaveDestructorTime(): void
    {
        $this->assertSame(7.0, $this->withDetails()->destructorTime());
    }

    public function testMayHaveFreeTime(): void
    {
        $this->assertSame(8.0, $this->withDetails()->freeTime());
    }

    public function testMayHaveProtected(): void
    {
        $this->assertTrue($this->withDetails()->isProtected());
    }

    public function testMayHaveFull(): void
    {
        $this->assertTrue($this->withDetails()->isFull());
    }

    public function testMayHaveBufferSize(): void
    {
        $this->assertSame(9, $this->withDetails()->bufferSize());
    }

    public function testMayNotHaveExtendedInformation(): void
    {
        $this->assertFalse($this->withoutDetails()->hasExtendedInformation());
    }

    public function testMayNotHaveApplicationTime(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->applicationTime();
    }

    public function testMayNotHaveCollectorTime(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->collectorTime();
    }

    public function testMayNotHaveDestructorTime(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->destructorTime();
    }

    public function testMayNotHaveFreeTime(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->freeTime();
    }

    public function testMayNotHaveRunning(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->isRunning();
    }

    public function testMayNotHaveProtected(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->isProtected();
    }

    public function testMayNotHaveFull(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->isFull();
    }

    public function testMayNotHaveBufferSize(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withoutDetails()->bufferSize();
    }

    private function withoutDetails(): GarbageCollectorStatus
    {
        return new GarbageCollectorStatus(
            1,
            2,
            3,
            4,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );
    }

    private function withDetails(): GarbageCollectorStatus
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
