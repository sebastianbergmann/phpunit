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
        $actual = (new Exception)->__sleep();

        $this->assertCount(5, $actual);
        $this->assertContains('serializableTrace', $actual);
        $this->assertContains('message', $actual);
        $this->assertContains('code', $actual);
        $this->assertContains('file', $actual);
        $this->assertContains('line', $actual);
    }
}
