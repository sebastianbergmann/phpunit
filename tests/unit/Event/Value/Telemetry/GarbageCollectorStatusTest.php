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
        $this->assertFalse($this->withoutDetails()->hasExtendedInformation());
        $this->assertTrue($this->withDetails()->hasExtendedInformation());
    }

    public function testMayHaveRunning(): void
    {
        $this->assertTrue($this->withDetails()->isRunning());
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
        $this->assertSame(5, $this->withDetails()->bufferSize());
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
            null
        );
    }

    private function withDetails(): GarbageCollectorStatus
    {
        return new GarbageCollectorStatus(
            1,
            2,
            3,
            4,
            true,
            true,
            true,
            5
        );
    }
}
