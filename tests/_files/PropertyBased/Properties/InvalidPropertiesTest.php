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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClassUsingDeepClone;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\ForAllExternal;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\RequireClassification;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\TestWithJson;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;

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
    #[TestWithJson('1')]
    public function testWithExplicitExampleThatIsNotAnArray(int $value): void
    {
    }

    #[Property]
    #[TestWith([1, 2])]
    public function testWithExplicitExampleWithMoreArgumentsThanParameters(int $value): void
    {
    }

    #[Property]
    #[TestWith([1])]
    public function testWithExplicitExampleWithoutArgumentForEveryParameter(int $a, int $b): void
    {
    }

    #[Property]
    #[TestWith(['a' => 1, 'b' => 2, 'c' => 3], 'unknown parameter')]
    public function testWithExplicitExampleWithArgumentForUnknownParameter(int $a, int $b): void
    {
    }

    #[Property]
    #[TestWith(['b' => 1, 2])]
    public function testWithExplicitExampleWithPositionalArgumentAfterNamedArgument(int $a, int $b): void
    {
    }

    #[Property]
    #[TestWith([1, 'a' => 2])]
    public function testWithExplicitExampleWithMoreThanOneArgumentForAParameter(int $a, int $b): void
    {
    }

    #[Property]
    #[TestWith([1], 'one')]
    #[TestWith([2], 'one')]
    public function testWithExplicitExamplesWithTheSameName(int $value): void
    {
    }

    #[Property]
    #[RequireClassification('', 10)]
    public function testWithRequiredClassificationWithEmptyLabel(int $value): void
    {
    }

    #[Property]
    #[RequireClassification('negative', 0)]
    public function testWithRequiredClassificationOfZeroPercent(int $value): void
    {
    }

    #[Property]
    #[RequireClassification('negative', 100.5)]
    public function testWithRequiredClassificationOfMoreThanOneHundredPercent(int $value): void
    {
    }

    #[Property]
    #[RequireClassification('negative', 10)]
    #[RequireClassification('negative', 20)]
    public function testWithRequiredClassificationsForTheSameLabel(int $value): void
    {
    }

    #[Property]
    #[Depends('testWithDataProvider')]
    public function testWithDepends(int $value): void
    {
    }

    #[Property]
    #[DependsOnClassUsingDeepClone(ValidPropertiesTest::class)]
    public function testWithVariantOfDepends(int $value): void
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
    public function testWithGeneratorMethodWhoseNameIsEmpty(#[ForAll('')] int $value): void
    {
    }

    #[Property]
    public function testWithGeneratorOfClassWhoseNameIsEmpty(#[ForAllExternal('', 'generator')] int $value): void
    {
    }

    #[Property]
    public function testWithGeneratorMethodOfAnotherClassWhoseNameIsEmpty(#[ForAllExternal(EmailAddressGenerators::class, '')] int $value): void
    {
    }

    #[Property]
    public function testWithGeneratorOfClassThatDoesNotExist(#[ForAllExternal('PHPUnit\TestFixture\PropertyBased\Properties\DoesNotExist', 'generator')] int $value): void
    {
    }

    #[Property]
    public function testWithGeneratorOfInterface(#[ForAllExternal(EmailAddress::class, 'asString')] int $value): void
    {
    }

    #[Property]
    public function testWithGeneratorOfTrait(#[ForAllExternal(GeneratorForEmailAddresses::class, 'emailAddresses')] int $value): void
    {
    }

    #[Property]
    public function testWithUnknownGeneratorMethodOfAnotherClass(#[ForAllExternal(EmailAddressGenerators::class, 'unknown')] int $value): void
    {
    }

    #[Property]
    public function testWithNonStaticGeneratorMethodOfAnotherClass(#[ForAllExternal(InvalidGenerators::class, 'nonStaticGenerator')] int $value): void
    {
    }

    #[Property]
    public function testWithAbstractGeneratorMethod(#[ForAllExternal(InvalidGenerators::class, 'abstractGenerator')] int $value): void
    {
    }

    #[Property]
    public function testWithGeneratorMethodWithRequiredParameters(#[ForAllExternal(InvalidGenerators::class, 'generatorWithRequiredParameter')] int $value): void
    {
    }

    #[Property]
    public function testWithUnsupportedType(mixed $value): void
    {
    }
}
