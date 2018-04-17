<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class ClonedDependencyTest extends TestCase
{
    private static $dependency;

    public static function setUpBeforeClass(): void
    {
        self::$dependency = new stdClass;
    }

    public function testOne()
    {
        $this->assertTrue(true);

        return self::$dependency;
    }

    /**
     * @depends testOne
     *
     * @param mixed $dependency
     */
    public function testTwo($dependency)
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    /**
     * @depends !clone testOne
     *
     * @param mixed $dependency
     */
    public function testThree($dependency)
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    /**
     * @depends clone testOne
     *
     * @param mixed $dependency
     */
    public function testFour($dependency)
    {
        $this->assertNotSame(self::$dependency, $dependency);
    }

    /**
     * @depends !shallowClone testOne
     *
     * @param mixed $dependency
     */
    public function testFive($dependency)
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    /**
     * @depends shallowClone testOne
     *
     * @param mixed $dependency
     */
    public function testSix($dependency)
    {
        $this->assertNotSame(self::$dependency, $dependency);
    }
}
