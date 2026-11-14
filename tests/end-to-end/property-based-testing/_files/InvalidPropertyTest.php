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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class InvalidPropertyTest extends TestCase
{
    #[Property]
    #[TestWith([1])]
    public function testHasAnExplicitExampleWithoutArgumentForEveryParameter(int $a, int $b): void
    {
    }

    #[Property]
    public function testHasAParameterOfAnUnsupportedType(mixed $value): void
    {
    }

    #[Property]
    #[Depends('testIsNotAProperty')]
    public function testDependsOnAnotherTest(int $value): void
    {
    }

    public function testIsNotAProperty(): void
    {
        $this->assertTrue(true);
    }
}
