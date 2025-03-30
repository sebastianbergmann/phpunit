<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Variable::class)]
#[Small]
final class VariableTest extends TestCase
{
    public function testHasName(): void
    {
        $this->assertSame('name', (new Variable('name', 'value', false))->name());
    }

    public function testHasValue(): void
    {
        $this->assertSame('value', (new Variable('name', 'value', false))->value());
    }

    public function testValueCanBeForced(): void
    {
        $this->assertFalse((new Variable('name', 'value', false))->force());
        $this->assertTrue((new Variable('name', 'value', true))->force());
    }
}
