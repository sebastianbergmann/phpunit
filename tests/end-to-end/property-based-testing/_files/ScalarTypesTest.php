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

use function is_finite;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ScalarTypesTest extends TestCase
{
    #[Property]
    public function testFloat(float $value): void
    {
        $this->assertTrue(is_finite($value));
    }

    #[Property]
    public function testBool(bool $value): void
    {
        $this->assertIsBool($value);
    }

    #[Property]
    public function testTrue(true $value): void
    {
        $this->assertTrue($value);
    }

    #[Property]
    public function testFalse(false $value): void
    {
        $this->assertFalse($value);
    }

    #[Property]
    public function testNull(null $value): void
    {
        $this->assertNull($value);
    }
}
