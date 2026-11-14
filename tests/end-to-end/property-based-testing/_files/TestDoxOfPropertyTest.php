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
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestDoxFormatter;
use PHPUnit\Framework\TestCase;

final class TestDoxOfPropertyTest extends TestCase
{
    public static function formatter(int $a): string
    {
        return 'Formatted ' . $a;
    }

    #[Property(examples: 3)]
    #[TestDox('Adding $a and $b is commutative')]
    public function testAddingIsCommutative(int $a, int $b): void
    {
        $this->assertSame($a + $b, $b + $a);
    }

    #[Property(examples: 3)]
    #[TestDoxFormatter('formatter')]
    public function testIsAnnotatedWithTestDoxFormatter(int $a): void
    {
        $this->assertIsInt($a);
    }
}
