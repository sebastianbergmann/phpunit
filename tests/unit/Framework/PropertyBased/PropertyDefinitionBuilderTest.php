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
use PHPUnit\TestFixture\PropertyBased\Properties\ClassWithoutGeneratorFor;
use PHPUnit\TestFixture\PropertyBased\Properties\DuplicateGeneratorForTest;
use PHPUnit\TestFixture\PropertyBased\Properties\DuplicateGeneratorForWithLeadingBackslashTest;
use PHPUnit\TestFixture\PropertyBased\Properties\EmailAddressGenerators;
use PHPUnit\TestFixture\PropertyBased\Properties\GeneratorForEmailAddresses;
use PHPUnit\TestFixture\PropertyBased\Properties\GeneratorForUnknownClassTest;
use PHPUnit\TestFixture\PropertyBased\Properties\InvalidGenerators;
use PHPUnit\TestFixture\PropertyBased\Properties\InvalidPropertiesTest;
use PHPUnit\TestFixture\PropertyBased\Properties\NonStaticGeneratorForTest;
use PHPUnit\TestFixture\PropertyBased\Properties\OtherEmailAddressGenerators;
use PHPUnit\TestFixture\PropertyBased\Properties\PropertyInSubclassTest;
use PHPUnit\TestFixture\PropertyBased\Properties\UseGeneratorsTest;
use PHPUnit\TestFixture\PropertyBased\Properties\UseGeneratorsWithOwnGeneratorForTest;
use PHPUnit\TestFixture\PropertyBased\Properties\ValidPropertiesTest;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

#[CoversClass(PropertyDefinitionBuilder::class)]
#[CoversClass(PropertyDefinition::class)]
#[CoversClass(ExplicitExample::class)]
#[CoversClass(GeneratorMethod::class)]
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
                '#[Property] cannot be combined with #[DataProvider], #[DataProviderClosure], or #[DataProviderExternal]',
            ],

            'explicit example that is not an array' => [
                InvalidPropertiesTest::class,
                'testWithExplicitExampleThatIsNotAnArray',
                'explicit example #0 is not an array, but int',
            ],

            'explicit example with more arguments than parameters' => [
                InvalidPropertiesTest::class,
                'testWithExplicitExampleWithMoreArgumentsThanParameters',
                'explicit example #0 has more arguments than the property has parameters',
            ],

            'explicit example without argument for every parameter' => [
                InvalidPropertiesTest::class,
                'testWithExplicitExampleWithoutArgumentForEveryParameter',
                'explicit example #0 has no argument for parameter $b',
            ],

            'explicit example with argument for unknown parameter' => [
                InvalidPropertiesTest::class,
                'testWithExplicitExampleWithArgumentForUnknownParameter',
                'explicit example "unknown parameter" has an argument for parameter $c, which does not exist',
            ],

            'explicit example with positional argument after named argument' => [
                InvalidPropertiesTest::class,
                'testWithExplicitExampleWithPositionalArgumentAfterNamedArgument',
                'explicit example #0 has a positional argument after a named argument',
            ],

            'explicit example with more than one argument for a parameter' => [
                InvalidPropertiesTest::class,
                'testWithExplicitExampleWithMoreThanOneArgumentForAParameter',
                'explicit example #0 has more than one argument for parameter $a',
            ],

            'required classification with an empty label' => [
                InvalidPropertiesTest::class,
                'testWithRequiredClassificationWithEmptyLabel',
                'the label that is passed to #[RequireClassification] must not be empty',
            ],

            'required classification of zero percent' => [
                InvalidPropertiesTest::class,
                'testWithRequiredClassificationOfZeroPercent',
                'the minimum percentage of the examples that are classified as "negative" must be greater than 0 and not greater than 100, 0 was given',
            ],

            'required classification of more than one hundred percent' => [
                InvalidPropertiesTest::class,
                'testWithRequiredClassificationOfMoreThanOneHundredPercent',
                'the minimum percentage of the examples that are classified as "negative" must be greater than 0 and not greater than 100, 100.5 was given',
            ],

            'required classifications for the same label' => [
                InvalidPropertiesTest::class,
                'testWithRequiredClassificationsForTheSameLabel',
                '#[RequireClassification] is used more than once for "negative"',
            ],

            'explicit examples with the same name' => [
                InvalidPropertiesTest::class,
                'testWithExplicitExamplesWithTheSameName',
                'explicit example "one" is specified more than once',
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

            'generator of a class that does not exist' => [
                InvalidPropertiesTest::class,
                'testWithGeneratorOfClassThatDoesNotExist',
                'the generator for parameter $value is specified as a method of PHPUnit\TestFixture\PropertyBased\Properties\DoesNotExist, which does not exist',
            ],

            'generator of an interface' => [
                InvalidPropertiesTest::class,
                'testWithGeneratorOfInterface',
                'the generator for parameter $value is specified as a method of ' . EmailAddress::class . ', which is an interface, not a class',
            ],

            'generator of a trait' => [
                InvalidPropertiesTest::class,
                'testWithGeneratorOfTrait',
                'the generator for parameter $value is specified as a method of ' . GeneratorForEmailAddresses::class . ', which is a trait, not a class',
            ],

            'unknown generator method of another class' => [
                InvalidPropertiesTest::class,
                'testWithUnknownGeneratorMethodOfAnotherClass',
                'the generator for parameter $value is specified as ' . EmailAddressGenerators::class . '::unknown(), which does not exist',
            ],

            'non-static generator method of another class' => [
                InvalidPropertiesTest::class,
                'testWithNonStaticGeneratorMethodOfAnotherClass',
                'the generator for parameter $value is specified as ' . InvalidGenerators::class . '::nonStaticGenerator(), which is not public and static',
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

            'classes used with #[UseGenerators] that register a generator for the same class' => [
                UseGeneratorsTest::class,
                'testUsesTwoClassesThatRegisterAGeneratorForTheSameClass',
                EmailAddressGenerators::class . '::emailAddresses() and ' . OtherEmailAddressGenerators::class . '::otherEmailAddresses() are both registered with #[GeneratorFor] for ' . EmailAddress::class,
            ],

            'class used with #[UseGenerators] that does not exist' => [
                UseGeneratorsTest::class,
                'testUsesAClassThatDoesNotExist',
                '#[UseGenerators] is used with PHPUnit\TestFixture\PropertyBased\Properties\DoesNotExist, which does not exist',
            ],

            'interface used with #[UseGenerators]' => [
                UseGeneratorsTest::class,
                'testUsesAnInterface',
                '#[UseGenerators] is used with ' . EmailAddress::class . ', which is an interface, not a class',
            ],

            'trait used with #[UseGenerators]' => [
                UseGeneratorsTest::class,
                'testUsesATrait',
                '#[UseGenerators] is used with ' . GeneratorForEmailAddresses::class . ', which is a trait, not a class',
            ],

            'class used with #[UseGenerators] that does not register a generator' => [
                UseGeneratorsTest::class,
                'testUsesAClassThatDoesNotRegisterAGenerator',
                'the class ' . ClassWithoutGeneratorFor::class . ' that is used with #[UseGenerators] does not register a generator with #[GeneratorFor]',
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
        $this->assertEquals(new GeneratorMethod(ValidPropertiesTest::class, 'smallIntegers'), $parameters['small']);
        $this->assertInstanceOf(AbstractGenerator::class, $parameters['name']);
        $this->assertInstanceOf(AbstractGenerator::class, $parameters['money']);
        $this->assertSame([], $definition->explicitExamples());
    }

    public function testBuildsTheRequiredClassificationsOfAProperty(): void
    {
        $this->assertSame(
            [
                ['label' => 'negative', 'minimumPercentage' => 10.0],
                ['label' => 'zero', 'minimumPercentage' => 100.0],
            ],
            (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithRequiredClassifications')->requiredClassifications(),
        );
    }

    public function testBuildsTheExplicitExamplesOfAPropertyWithTheirArgumentsByParameterName(): void
    {
        $explicitExamples = (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithExplicitExamples')->explicitExamples();

        $this->assertCount(4, $explicitExamples);

        $this->assertSame(0, $explicitExamples[0]->name());
        $this->assertSame(['small' => 1, 'name' => 'one'], $explicitExamples[0]->arguments());

        $this->assertSame('named arguments', $explicitExamples[1]->name());
        $this->assertSame(['small' => 2, 'name' => 'two'], $explicitExamples[1]->arguments());

        $this->assertSame(1, $explicitExamples[2]->name());
        $this->assertSame(['small' => 3, 'name' => 'three'], $explicitExamples[2]->arguments());

        $this->assertSame(2, $explicitExamples[3]->name());
        $this->assertSame(['small' => 4, 'name' => 'four'], $explicitExamples[3]->arguments());
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
        $this->assertEquals(
            [EmailAddress::class => new GeneratorMethod(ValidPropertiesTest::class, 'emailAddresses')],
            (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithRegisteredGenerator')->registeredGenerators(),
        );
    }

    public function testCollectsTheGeneratorsRegisteredWithGeneratorForThatAreDeclaredInAParentClass(): void
    {
        $this->assertEquals(
            [EmailAddress::class => new GeneratorMethod(PropertyInSubclassTest::class, 'emailAddresses')],
            (new PropertyDefinitionBuilder)->build(PropertyInSubclassTest::class, 'testOne')->registeredGenerators(),
        );
    }

    public function testTheGeneratorForAParameterMayBeSpecifiedWithAMethodOfAnotherClass(): void
    {
        $this->assertEquals(
            new GeneratorMethod(EmailAddressGenerators::class, 'emailAddresses'),
            (new PropertyDefinitionBuilder)->build(ValidPropertiesTest::class, 'testWithGeneratorOfAnotherClass')->parameters()['emailAddress'],
        );
    }

    public function testUsesTheGeneratorsThatAClassThatIsUsedForTheTestCaseClassRegisters(): void
    {
        $this->assertEquals(
            [EmailAddress::class => new GeneratorMethod(EmailAddressGenerators::class, 'emailAddresses')],
            (new PropertyDefinitionBuilder)->build(UseGeneratorsTest::class, 'testUsesTheGeneratorsOfTheClassThatIsUsedForTheTestCaseClass')->registeredGenerators(),
        );
    }

    public function testTheGeneratorsThatAClassThatIsUsedForThePropertyRegistersTakePrecedenceOverThoseOfAClassThatIsUsedForTheTestCaseClass(): void
    {
        $this->assertEquals(
            [EmailAddress::class => new GeneratorMethod(OtherEmailAddressGenerators::class, 'otherEmailAddresses')],
            (new PropertyDefinitionBuilder)->build(UseGeneratorsTest::class, 'testUsesTheGeneratorsOfTheClassThatIsUsedForTheProperty')->registeredGenerators(),
        );
    }

    public function testTheGeneratorsThatTheTestCaseClassRegistersTakePrecedenceOverThoseOfTheClassesThatAreUsed(): void
    {
        $this->assertEquals(
            [EmailAddress::class => new GeneratorMethod(UseGeneratorsWithOwnGeneratorForTest::class, 'emailAddresses')],
            (new PropertyDefinitionBuilder)->build(UseGeneratorsWithOwnGeneratorForTest::class, 'testUsesTheGeneratorOfTheTestCaseClass')->registeredGenerators(),
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
