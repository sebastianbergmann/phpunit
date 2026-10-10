<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function array_keys;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Metadata\Attribute\ForAllClosureTest;
use PHPUnit\TestFixture\PropertyBased\Derivation\Customer;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;
use PHPUnit\TestFixture\PropertyBased\Derivation\ExampleEmailAddress;
use PHPUnit\TestFixture\PropertyBased\Properties\DuplicateGeneratorForTest;
use PHPUnit\TestFixture\PropertyBased\Properties\DuplicateGeneratorForWithLeadingBackslashTest;
use PHPUnit\TestFixture\PropertyBased\Properties\GeneratorForUnknownClassTest;
use PHPUnit\TestFixture\PropertyBased\Properties\InvalidPropertiesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\NonStaticGeneratorForTest;
use PHPUnit\TestFixture\PropertyBased\Properties\PropertyInSubclassTest;
use PHPUnit\TestFixture\PropertyBased\Properties\ValidPropertiesTest;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(PropertyDefinitionBuilder::class)]
#[CoversClass(PropertyDefinition::class)]
#[CoversClass(InvalidPropertyException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class PropertyDefinitionBuilderTest extends TestCase
{
    /**
     * @return array<string, array{class-string<TestCase>, non-empty-string, string}>
     */
    public static function invalidPropertyProvider(): array
    {
        return [
            'data provider' => [
                InvalidPropertiesTest::class,
                'testWithDataProvider',
                '#[Property] cannot be combined with #[DataProvider], #[DataProviderClosure], #[DataProviderExternal], #[TestWith], or #[TestWithJson]',
            ],

            'test with' => [
                InvalidPropertiesTest::class,
                'testWithTestWith',
                '#[Property] cannot be combined with #[DataProvider], #[DataProviderClosure], #[DataProviderExternal], #[TestWith], or #[TestWithJson]',
            ],

            'depends' => [
                InvalidPropertiesTest::class,
                'testWithDepends',
                '#[Property] cannot be combined with #[Depends] or any of its variants',
            ],

            'variant of depends' => [
                InvalidPropertiesTest::class,
                'testWithVariantOfDepends',
                '#[Property] cannot be combined with #[Depends] or any of its variants',
            ],

            'variadic parameter' => [
                InvalidPropertiesTest::class,
                'testWithVariadicParameter',
                'variadic parameter $values is not supported',
            ],

            'unknown generator method' => [
                InvalidPropertiesTest::class,
                'testWithUnknownGeneratorMethod',
                'the generator for parameter $value is specified as ' . InvalidPropertiesTest::class . '::unknown(), which does not exist',
            ],

            'non-static generator method' => [
                InvalidPropertiesTest::class,
                'testWithNonStaticGeneratorMethod',
                'the generator for parameter $value is specified as ' . InvalidPropertiesTest::class . '::nonStaticGenerator(), which is not public and static',
            ],

            'unsupported type' => [
                InvalidPropertiesTest::class,
                'testWithUnsupportedType',
                'cannot derive a generator for parameter $value of type mixed: mixed is not supported',
            ],

            'duplicate registered generators' => [
                DuplicateGeneratorForTest::class,
                'testOne',
                DuplicateGeneratorForTest::class . '::first() and ' . DuplicateGeneratorForTest::class . '::second() are both registered with #[GeneratorFor] for ' . EmailAddress::class,
            ],

            'duplicate registered generators, one for the class name with a leading backslash' => [
                DuplicateGeneratorForWithLeadingBackslashTest::class,
                'testOne',
                DuplicateGeneratorForWithLeadingBackslashTest::class . '::first() and ' . DuplicateGeneratorForWithLeadingBackslashTest::class . '::second() are both registered with #[GeneratorFor] for ' . EmailAddress::class,
            ],

            'non-static registered generator' => [
                NonStaticGeneratorForTest::class,
                'testOne',
                NonStaticGeneratorForTest::class . '::emailAddresses() is registered with #[GeneratorFor] for ' . EmailAddress::class . ', but it is not public and static',
            ],

            'generator registered for unknown class' => [
                GeneratorForUnknownClassTest::class,
                'testOne',
                GeneratorForUnknownClassTest::class . '::unknown() is registered with #[GeneratorFor] for Unknown, which is neither a class nor an interface',
            ],
        ];
    }

    public function testBuildsTheDefinitionOfAProperty(): void
    {
        $definition = (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithParameters');

        $this->assertSame(ValidPropertiesTest::class, $definition->className());
        $this->assertSame('testWithParameters', $definition->methodName());

        $parameters = $definition->parameters();

        $this->assertSame(['small', 'name', 'money'], array_keys($parameters));
        $this->assertSame('smallIntegers', $parameters['small']);
        $this->assertInstanceOf(AbstractGenerator::class, $parameters['name']);
        $this->assertInstanceOf(AbstractGenerator::class, $parameters['money']);
    }

    #[RequiresPhp('^8.5')]
    public function testTheGeneratorForAParameterMayBeSpecifiedWithAClosure(): void
    {
        $parameters = (new PropertyDefinitionBuilder)->build(ForAllClosureTest::class, 'testOne')->parameters();

        $this->assertArrayHasKey('a', $parameters);
        $this->assertInstanceOf(Closure::class, $parameters['a']);
    }

    public function testAPropertyMayHaveNoParameters(): void
    {
        $this->assertSame([], (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithoutParameters')->parameters());
    }

    public function testCollectsTheGeneratorsRegisteredWithGeneratorForIncludingThoseDeclaredInTraits(): void
    {
        $this->assertSame(
            [EmailAddress::class => 'emailAddresses'],
            (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithRegisteredGenerator')->registeredGenerators(),
        );
    }

    public function testCollectsTheGeneratorsRegisteredWithGeneratorForThatAreDeclaredInAParentClass(): void
    {
        $this->assertSame(
            [EmailAddress::class => 'emailAddresses'],
            (new PropertyDefinitionBuilder)->build(PropertyInSubclassTest::class, 'testOne')->registeredGenerators(),
        );
    }

    public function testTheGeneratorsThatAreDerivedFromTypesUseTheRegisteredGeneratorsOfTheDeriverOfTheChoiceSource(): void
    {
        $parameters = (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithRegisteredGenerator')->parameters();

        $this->assertInstanceOf(AbstractGenerator::class, $parameters['customer']);
        $this->assertInstanceOf(AbstractGenerator::class, $parameters['emailAddress']);

        $emailAddress = new ExampleEmailAddress;
        $source       = ChoiceSource::random(
            new Randomizer(new Xoshiro256StarStar(1234)),
            ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES,
            new GeneratorDeriver(GeneratorRegistry::from([EmailAddress::class => Generators::constant($emailAddress)])),
        );

        $customer = $parameters['customer']->generate($source);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame($emailAddress, $customer->emailAddress());
        $this->assertSame($emailAddress, $parameters['emailAddress']->generate($source));
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    #[DataProvider('invalidPropertyProvider')]
    public function testReportsPropertiesThatCannotBeChecked(string $className, string $methodName, string $reason): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property ' . $className . '::' . $methodName . ' cannot be checked: ' . $reason);

        (new PropertyDefinitionBuilder)->build($className, $methodName);
    }
}
