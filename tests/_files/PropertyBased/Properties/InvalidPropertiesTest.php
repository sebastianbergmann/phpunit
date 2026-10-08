<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Properties;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class InvalidPropertiesTest extends TestCase
{
    /**
     * @return array<string, array{int}>
     */
    public static function provider(): array
    {
        return ['one' => [1]];
    }

    /**
     * @return ValueGenerator<int>
     */
    public function nonStaticGenerator(): ValueGenerator
    {
        return Generators::integers();
    }

    #[Property]
    #[DataProvider('provider')]
    public function testWithDataProvider(int $value): void
    {
    }

    #[Property]
    #[TestWith([1])]
    public function testWithTestWith(int $value): void
    {
    }

    #[Property]
    public function testWithVariadicParameter(int ...$values): void
    {
    }

    #[Property]
    public function testWithUnknownGeneratorMethod(#[ForAll('unknown')] int $value): void
    {
    }

    #[Property]
    public function testWithNonStaticGeneratorMethod(#[ForAll('nonStaticGenerator')] int $value): void
    {
    }

    #[Property]
    public function testWithUnsupportedType(mixed $value): void
    {
    }
}
