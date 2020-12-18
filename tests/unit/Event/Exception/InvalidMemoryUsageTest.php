<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Exception;

use function sprintf;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Exception\InvalidMemoryUsage
 */
final class InvalidMemoryUsageTest extends TestCase
{
    public function testBytesReturnsInvalidMemoryUsage(): void
    {
        $bytes = -9000;

        $exception = InvalidMemoryUsage::bytes($bytes);

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);

        $message = sprintf(
            '%d is not a valid memory usage in bytes.',
            $bytes
        );

        $this->assertSame($message, $exception->getMessage());
    }
}
