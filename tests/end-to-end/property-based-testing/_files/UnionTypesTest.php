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

use function is_int;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class UnionTypesTest extends TestCase
{
    #[Property]
    public function testNullable(?int $value): void
    {
        $this->assertTrue($value === null || is_int($value));
    }

    #[Property]
    public function testUnionWithNull(?int $value): void
    {
        $this->assertTrue($value === null || is_int($value));
    }

    #[Property]
    public function testUnion(int|string $value): void
    {
        $this->assertIsScalar($value);
    }
}
