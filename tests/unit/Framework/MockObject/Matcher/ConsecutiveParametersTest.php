<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class ConsecutiveParametersTest extends TestCase
{
    public function testIntegration(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar'],
                 [21, 42]
             );

        $this->assertNull($mock->foo('bar'));
        $this->assertNull($mock->foo(21, 42));
    }

    public function testIntegrationWithLessAssertionsThanMethodCalls(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar']
             );

        $this->assertNull($mock->foo('bar'));
        $this->assertNull($mock->foo(21, 42));
    }

    public function testIntegrationExpectingException(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar'],
                 [21, 42]
             );

        $mock->foo('bar');

        $this->expectException(ExpectationFailedException::class);

        $mock->foo('invalid');
    }

    /**
     * Test for the issue https://github.com/sebastianbergmann/phpunit/issues/3590
     */
    public function testMutableObjectsChangeSuccess(): void
    {
        /** @var \DateTime|MockObject $mock */
        $mock = $this
            ->getMockBuilder(\DateTime::class)
            ->setMethods(['diff'])
            ->getMock();

        $validationValues = [];

        $mock
            ->expects($this->exactly(2))
            ->method('diff')
            ->withConsecutive([
                $this->callback(function (\DateTime $it) use (&$validationValues) {
                    $validationValues[0] = $it->format('Y');
                    return $it->format('Y') === '2019';
                })],
                [$this->callback(function (\DateTime $it) use (&$validationValues) {
                    $validationValues[1] = $it->format('Y');
                    return $it->format('Y') === '1970';
                })]
            );

        $arg = \DateTime::createFromFormat('Y-m-d', '2019-01-01');

        $mock->diff($arg);
        $arg->setDate(1970, 4, 5);
        $mock->diff($arg);

        $this->assertEquals([0 => 2019, 1 => 1970], $validationValues);
    }
}
