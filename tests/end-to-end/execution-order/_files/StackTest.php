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

use function array_pop;
use function end;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class StackTest extends TestCase
{
    public function testPush()
    {
        $stack = [];
        $this->assertCount(0, $stack);

        $stack[] = 'foo';
        $this->assertEquals('foo', end($stack));
        $this->assertCount(1, $stack);

        return $stack;
    }

    #[Depends('testPush')]
    public function testPop(array $stack): void
    {
        $this->assertEquals('foo', array_pop($stack));
        $this->assertCount(0, $stack);
    }
}
