<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

class MatcherTest extends TestCase
{
    public function testInvokedAtIndexCountsSpecifiedMethodOnly(): void
    {
        // Mock a class with at least two methods.
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->getMock();

        // First time the later called method gets invoked, return a predefined result.
        $mock->expects($this->at(0))
             ->method('doSomethingElse')
             ->will($this->returnValue('mocked result'));

        // Invoke unconstrained method
        $mock->doSomething('a', 'b');

        // Invoke mocked method first time.
        $this->assertEquals('mocked result', $mock->doSomethingElse('c'));
    }

    public function testInvokedCountCountsSpecifiedMethodOnly(): void
    {
        // Mock a class with at least two methods.
        $mock = $this->getMockBuilder(SomeClass::class)
                     ->getMock();

        // Expect the later called method be invoked once.
        $mock->expects($this->once())
             ->method('doSomethingElse');

        // Invoke unconstrained method
        $mock->doSomething('a', 'b');

        // Invoke mocked method once.
        $mock->doSomethingElse('c');
    }
}
