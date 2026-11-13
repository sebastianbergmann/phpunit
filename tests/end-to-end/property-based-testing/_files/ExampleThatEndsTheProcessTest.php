<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;

final class ExampleThatEndsTheProcessTest extends TestCase
{
    /**
     * @param int<1000, 2000> $value
     */
    #[Property]
    public function testCompilesCodeThatCannotBeCompiled(int $value): void
    {
        $name = $this->draw(Generators::elements('this'), 'name');

        eval('function f() { $' . $name . ' = ' . $value . '; }');
    }

    /**
     * @param int<1000, 2000> $value
     */
    #[Property]
    #[TestWith([500])]
    public function testCompilesCodeThatCannotBeCompiledForExplicitExample(int $value): void
    {
        if ($value === 500) {
            eval('function g() { $this = ' . $value . '; }');
        }

        $this->assertGreaterThan(0, $value);
    }

    /**
     * @param int<1000, 2000> $value
     */
    #[Property]
    public function testExits(int $value): void
    {
        exit(1);
    }
}
