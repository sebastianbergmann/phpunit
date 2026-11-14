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
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Properties\GeneratorThatDoesNotReturnAValueGeneratorTest;
use PHPUnit\TestFixture\PropertyBased\Properties\PropertyWithUnmetRequirementsTest;
use PHPUnit\TestFixture\PropertyBased\Properties\ValidPropertiesTest;

#[CoversClass(GeneratorResolver::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class GeneratorResolverTest extends TestCase
{
    /**
     * @return array<string, array{non-empty-string, non-empty-string}>
     */
    public static function generatorThatDoesNotReturnAValueGeneratorProvider(): array
    {
        return [
            'generator method' => ['testUsesAGeneratorMethod', '::integers() does not return a ' . ValueGenerator::class],
            'closure'          => ['testUsesAClosure', 'the closure that is specified with #[ForAll] for parameter $value does not return a ' . ValueGenerator::class],
        ];
    }

    public function testResolvesTheGeneratorsOfTheParametersOfAProperty(): void
    {
        $definition = new PropertyDefinitionBuilder()->build(ValidPropertiesTest::class, 'testWithParameters');
        $resolver   = new GeneratorResolver($definition);

        [, $generators] = $resolver->generators(new ValidPropertiesTest('testWithParameters'), $this->createStub(Emitter::class));

        $this->assertSame($definition, $resolver->definition());
        $this->assertSame(['small', 'name', 'money'], array_keys($generators));
        $this->assertContainsOnlyInstancesOf(AbstractGenerator::class, $generators);
    }

    public function testResolvesTheGeneratorsOfAPropertyThatUsesARegisteredGenerator(): void
    {
        $resolver = new GeneratorResolver(new PropertyDefinitionBuilder()->build(ValidPropertiesTest::class, 'testWithRegisteredGenerator'));

        [, $generators] = $resolver->generators(new ValidPropertiesTest('testWithRegisteredGenerator'), $this->createStub(Emitter::class));

        $this->assertSame(['customer', 'emailAddress'], array_keys($generators));
    }

    public function testBuildsTheDefinitionOfAPropertyWhoseRequirementsAreMetWhenItRuns(): void
    {
        $resolver = new GeneratorResolver(null);

        [, $generators] = $resolver->generators(new ValidPropertiesTest('testWithoutParameters'), $this->createStub(Emitter::class));

        $this->assertSame([], $generators);
        $this->assertNotNull($resolver->definition());
        $this->assertSame('testWithoutParameters', $resolver->definition()->methodName());
    }

    public function testResolvesNoGeneratorsWhileTheRequirementsOfThePropertyAreNotMet(): void
    {
        $resolver = new GeneratorResolver(null);

        [, $generators] = $resolver->generators(new PropertyWithUnmetRequirementsTest('testOne'), $this->createStub(Emitter::class));

        $this->assertSame([], $generators);
        $this->assertNull($resolver->definition());
    }

    /**
     * @param non-empty-string $methodName
     * @param non-empty-string $message
     */
    #[DataProvider('generatorThatDoesNotReturnAValueGeneratorProvider')]
    public function testRejectsAGeneratorThatDoesNotReturnAValueGenerator(string $methodName, string $message): void
    {
        $resolver = new GeneratorResolver(new PropertyDefinitionBuilder()->build(GeneratorThatDoesNotReturnAValueGeneratorTest::class, $methodName));

        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessageIsOrContains($message);

        $resolver->generators(new GeneratorThatDoesNotReturnAValueGeneratorTest($methodName), $this->createStub(Emitter::class));
    }
}
