<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use function array_shift;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\CoversClassOnClassTest;
use PHPUnit\TestFixture\CoversNothingOnClassTest;
use PHPUnit\TestFixture\CoversNothingOnMethodTest;
use PHPUnit\TestFixture\Metadata\Attribute\CoversTest;
use PHPUnit\TestFixture\Metadata\Attribute\UsesTest;
use PHPUnit\TestFixture\NoCoverageAttributesTest;
use SebastianBergmann\CodeCoverage\Test\Target\Class_;
use SebastianBergmann\CodeCoverage\Test\Target\ClassesThatExtendClass;
use SebastianBergmann\CodeCoverage\Test\Target\ClassesThatImplementInterface;
use SebastianBergmann\CodeCoverage\Test\Target\Function_;
use SebastianBergmann\CodeCoverage\Test\Target\Method;
use SebastianBergmann\CodeCoverage\Test\Target\Namespace_;
use SebastianBergmann\CodeCoverage\Test\Target\Trait_;

#[CoversClass(CodeCoverage::class)]
#[Small]
#[Group('metadata')]
final class CodeCoverageTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: bool, 1: class-string}>
     */
    public static function canSkipCoverageProvider(): array
    {
        return [
            [false, NoCoverageAttributesTest::class],
            [false, CoversClassOnClassTest::class],
            [true, CoversNothingOnClassTest::class],
            [true, CoversNothingOnMethodTest::class],
        ];
    }

    #[TestDox('Maps #[Covers*()] metadata to phpunit/php-code-coverage TargetCollection')]
    public function testMapsCoversMetadataToCodeCoverageTargetCollection(): void
    {
        $targets = (new CodeCoverage)->coversTargets(CoversTest::class, 'testOne');

        $this->assertNotFalse($targets);
        $this->assertCount(7, $targets);

        $targets = $targets->asArray();

        $target = array_shift($targets);
        $this->assertInstanceOf(Namespace_::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute', $target->namespace());

        $target = array_shift($targets);
        $this->assertInstanceOf(Class_::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->className());

        $target = array_shift($targets);
        $this->assertInstanceOf(ClassesThatExtendClass::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->className());

        $target = array_shift($targets);
        $this->assertInstanceOf(ClassesThatImplementInterface::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->interfaceName());

        $target = array_shift($targets);
        $this->assertInstanceOf(Trait_::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\ExampleTrait', $target->traitName());

        $target = array_shift($targets);
        $this->assertInstanceOf(Method::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->className());
        $this->assertSame('method', $target->methodName());

        $target = array_shift($targets);
        $this->assertInstanceOf(Function_::class, $target);
        $this->assertSame('f', $target->functionName());
    }

    #[TestDox('Maps #[Uses*()] metadata to phpunit/php-code-coverage TargetCollection')]
    public function testMapsUsesMetadataToCodeCoverageTargetCollection(): void
    {
        $targets = (new CodeCoverage)->usesTargets(UsesTest::class, 'testOne');

        $this->assertNotFalse($targets);
        $this->assertCount(7, $targets);

        $targets = $targets->asArray();

        $target = array_shift($targets);
        $this->assertInstanceOf(Namespace_::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute', $target->namespace());

        $target = array_shift($targets);
        $this->assertInstanceOf(Class_::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->className());

        $target = array_shift($targets);
        $this->assertInstanceOf(ClassesThatExtendClass::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->className());

        $target = array_shift($targets);
        $this->assertInstanceOf(ClassesThatImplementInterface::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->interfaceName());

        $target = array_shift($targets);
        $this->assertInstanceOf(Trait_::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\ExampleTrait', $target->traitName());

        $target = array_shift($targets);
        $this->assertInstanceOf(Method::class, $target);
        $this->assertSame('PHPUnit\TestFixture\Metadata\Attribute\Example', $target->className());
        $this->assertSame('method', $target->methodName());

        $target = array_shift($targets);
        $this->assertInstanceOf(Function_::class, $target);
        $this->assertSame('f', $target->functionName());
    }

    /**
     * @param class-string $testCase
     */
    #[DataProvider('canSkipCoverageProvider')]
    public function testWhetherCollectionOfCodeCoverageDataCanBeSkippedCanBeDetermined(bool $expected, string $testCase): void
    {
        $test             = new $testCase('testSomething');
        $coverageRequired = (new CodeCoverage)->shouldCodeCoverageBeCollectedFor($test::class, $test->name());
        $canSkipCoverage  = !$coverageRequired;

        $this->assertSame($expected, $canSkipCoverage);
    }
}
