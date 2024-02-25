<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;
use stdClass;

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

    #[Depends('testOne')]
    public function testTwo($dependency): void
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    #[Depends('testOne')]
    public function testThree($dependency): void
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    #[DependsUsingDeepClone('testOne')]
    public function testFour($dependency): void
    {
        $this->assertNotSame(self::$dependency, $dependency);
    }

    #[Depends('testOne')]
    public function testFive($dependency): void
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    #[DependsUsingShallowClone('testOne')]
    public function testSix($dependency): void
    {
        $this->assertNotSame(self::$dependency, $dependency);
    }
}
