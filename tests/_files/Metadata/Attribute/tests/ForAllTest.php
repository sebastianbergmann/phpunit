<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class ForAllTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function smallIntegers(): ValueGenerator
    {
        return Generators::integers(0, 9);
    }

    #[Property]
    public function testOne(#[ForAll('smallIntegers')] int $a, int $b): void
    {
    }

    #[Property]
    public function testWithGeneratorOfAnotherClass(#[ForAll([self::class, 'smallIntegers'])] int $a): void
    {
    }

    #[Property]
    public function testWithInvalidForAll(#[ForAll(42)] int $a): void
    {
    }
}
