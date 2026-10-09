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

use function is_numeric;
use function mb_check_encoding;
use function strtolower;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class StringTypesTest extends TestCase
{
    #[Property]
    public function testString(string $value): void
    {
        $this->assertTrue(mb_check_encoding($value, 'UTF-8'));
    }

    /**
     * @param non-empty-string $value
     */
    #[Property]
    public function testNonEmptyString(string $value): void
    {
        $this->assertNotSame('', $value);
        $this->assertTrue(mb_check_encoding($value, 'UTF-8'));
    }

    /**
     * @param non-falsy-string $value
     */
    #[Property]
    public function testNonFalsyString(string $value): void
    {
        $this->assertNotSame('', $value);
        $this->assertNotSame('0', $value);
    }

    /**
     * @param numeric-string $value
     */
    #[Property]
    public function testNumericString(string $value): void
    {
        $this->assertTrue(is_numeric($value));
    }

    /**
     * @param lowercase-string $value
     */
    #[Property]
    public function testLowercaseString(string $value): void
    {
        $this->assertSame(strtolower($value), $value);
    }
}
