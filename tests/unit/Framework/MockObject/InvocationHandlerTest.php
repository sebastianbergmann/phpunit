<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\StringableClass;
use RuntimeException;

class InvocationHandlerTest extends TestCase
{
    public function testExceptionThrownIn__ToStringIsDeferred(): void
    {
        $mock = $this->createMock(StringableClass::class);
        $mock->method('__toString')
            ->willThrowException(new RuntimeException('planned error'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('planned error');
        $mock->__toString();
    }
}
