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

class MultiDependencyTest extends TestCase
{
    public function testOne()
    {
        $this->assertTrue(true);

        return 'foo';
    }

    public function testTwo()
    {
        $this->assertTrue(true);

        return 'bar';
    }

    /**
     * @depends testOne
     * @depends testTwo
     */
    public function testThree($a, $b): void
    {
        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
    }

    /**
     * @depends MultiDependencyTest::testThree
     */
    public function testFour(): void
    {
        $this->assertTrue(true);
    }

    public function testFive(): void
    {
        $this->assertTrue(true);
    }
}
