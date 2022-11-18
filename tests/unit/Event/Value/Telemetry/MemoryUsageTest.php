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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoryUsage::class)]
#[Small]
final class MemoryUsageTest extends TestCase
{
    public static function provideValidBytes(): array
    {
        return [
            'int-less-than-zero'    => [-1],
            'int-zero'              => [0],
            'int-greater-than-zero' => [1],
        ];
    }

    #[DataProvider('provideValidBytes')]
    public function testFromBytesReturnsMemoryUsage(int $bytes): void
    {
        $memoryUsage = MemoryUsage::fromBytes($bytes);

        $this->assertSame($bytes, $memoryUsage->bytes());
    }

    public function testDiffReturnsMemoryUsage(): void
    {
        $one = MemoryUsage::fromBytes(2000);
        $two = MemoryUsage::fromBytes(3000);

        $diff = $one->diff($two);

        $this->assertNotSame($one, $diff);
        $this->assertNotSame($two, $diff);
        $this->assertSame($one->bytes() - $two->bytes(), $diff->bytes());
    }
}
