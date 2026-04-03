<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DeprecatedExpectExceptionMessageTest extends TestCase
{
    public function testExpectExceptionMessage(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('expected message');

        throw new RuntimeException('expected message');
    }
}
