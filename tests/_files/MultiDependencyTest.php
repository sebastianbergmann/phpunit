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
use PHPUnit\Framework\Attributes\DependsExternal;
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

    #[Depends('testOne')]
    #[Depends('testTwo')]
    public function testThree($a, $b): void
    {
        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
    }

    #[DependsExternal(self::class, 'testThree')]
    public function testFour(): void
    {
        $this->assertTrue(true);
    }

    public function testFive(): void
    {
        $this->assertTrue(true);
    }
}
