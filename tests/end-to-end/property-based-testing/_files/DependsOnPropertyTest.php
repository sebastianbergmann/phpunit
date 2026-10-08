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

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class DependsOnPropertyTest extends TestCase
{
    #[Property(examples: 10)]
    public function testHolds(int $value): void
    {
        $this->assertIsInt($value);
    }

    #[Property(examples: 10)]
    public function testDoesNotHold(int $value): void
    {
        $this->assertIsString($value);
    }

    #[Depends('testHolds')]
    public function testDependsOnPropertyThatHolds(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testDoesNotHold')]
    public function testDependsOnPropertyThatDoesNotHold(): void
    {
        $this->assertTrue(true);
    }
}
