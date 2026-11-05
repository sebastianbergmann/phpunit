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

use function array_diff;
use function array_is_list;
use function array_keys;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ArrayTypesTest extends TestCase
{
    /**
     * @param list<int> $value
     */
    #[Property]
    public function testList(array $value): void
    {
        $this->assertTrue(array_is_list($value));
        $this->assertContainsOnlyInt($value);
    }

    /**
     * @param non-empty-list<int> $value
     */
    #[Property]
    public function testNonEmptyList(array $value): void
    {
        $this->assertNotEmpty($value);
        $this->assertTrue(array_is_list($value));
        $this->assertContainsOnlyInt($value);
    }

    /**
     * @param array<string, int> $value
     */
    #[Property]
    public function testArrayWithKeyType(array $value): void
    {
        $this->assertContainsOnlyInt($value);
    }

    /**
     * @param non-empty-array<int, string> $value
     */
    #[Property]
    public function testNonEmptyArray(array $value): void
    {
        $this->assertNotEmpty($value);
        $this->assertContainsOnlyString($value);
        $this->assertContainsOnlyInt(array_keys($value));
    }

    #[Property]
    public function testArray(array $value): void
    {
        $this->assertIsArray($value);
    }

    /**
     * @param array{a: int, b?: string} $value
     */
    #[Property]
    public function testArrayShape(array $value): void
    {
        $this->assertArrayHasKey('a', $value);
        $this->assertIsInt($value['a']);
        $this->assertSame([], array_diff(array_keys($value), ['a', 'b']));

        if (isset($value['b'])) {
            $this->assertIsString($value['b']);
        }
    }
}
