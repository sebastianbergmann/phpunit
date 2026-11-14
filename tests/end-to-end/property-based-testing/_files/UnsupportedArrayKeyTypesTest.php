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

use function array_keys;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Currency.php';

final class UnsupportedArrayKeyTypesTest extends TestCase
{
    /**
     * @param array<bool, int> $value
     */
    #[Property]
    public function testBooleanKeys(array $value): void
    {
    }

    /**
     * @param array<float, int> $value
     */
    #[Property]
    public function testFloatingPointKeys(array $value): void
    {
    }

    /**
     * @param array<Currency, int> $value
     */
    #[Property]
    public function testCasesOfAnEnumerationAsKeys(array $value): void
    {
    }

    /**
     * @param array<?int, int> $value
     */
    #[Property]
    public function testNullableKeys(array $value): void
    {
    }

    /**
     * @param array<float|int, int> $value
     */
    #[Property]
    public function testKeysOfAUnionWithFloat(array $value): void
    {
    }

    /**
     * @param non-empty-array<'1', int> $value
     */
    #[Property]
    public function testStringOfAnIntegerAsKey(array $value): void
    {
    }

    /**
     * @param non-empty-array<'a'|int<1, 3>|non-empty-string, int> $value
     */
    #[Property]
    public function testKeysThatAreIntegersOrStrings(array $value): void
    {
        foreach (array_keys($value) as $key) {
            $this->assertNotSame('', $key);
        }
    }
}
