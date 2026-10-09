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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class InvalidPropertyDeclarationsTest extends TestCase
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
    public function testIsCombinedWithADataProvider(int $value): void
    {
    }

    #[Property]
    public function testHasAVariadicParameter(int ...$values): void
    {
    }

    #[Property]
    public function testSpecifiesAGeneratorMethodThatDoesNotExist(#[ForAll('doesNotExist')] int $value): void
    {
    }

    #[Property]
    public function testSpecifiesAGeneratorMethodThatIsNotStatic(#[ForAll('nonStaticGenerator')] int $value): void
    {
    }

    /**
     * @param list<int $value
     */
    #[Property]
    public function testHasAParameterWithAnUnparsableType(array $value): void
    {
    }
}
