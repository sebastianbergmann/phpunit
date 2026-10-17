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
use PHPUnit\Framework\TestCase;

final class LiteralTypesTest extends TestCase
{
    /**
     * @param 'a' $value
     */
    #[Property]
    public function testStringLiteral(string $value): void
    {
        $this->assertSame('a', $value);
    }

    /**
     * @param 42 $value
     */
    #[Property]
    public function testIntegerLiteral(int $value): void
    {
        $this->assertSame(42, $value);
    }

    /**
     * @param 'a'|'b' $value
     */
    #[Property]
    public function testUnionOfLiterals(string $value): void
    {
        $this->assertContains($value, ['a', 'b']);
    }
}
