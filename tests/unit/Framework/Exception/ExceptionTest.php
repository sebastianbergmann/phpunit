<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

class ExceptionTest extends TestCase
{
    public function testExceptionSleep(): void
    {
        $exception = new Exception();

        $expectedArray = [
            'serializableTrace',
            'message',
            'code',
            'file',
            'line',
        ];

        $this->assertSame($expectedArray, $exception->__sleep());
    }
}
