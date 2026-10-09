<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use function file_put_contents;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Currency;
use PHPUnit\TestFixture\PropertyBased\EnumerationWithoutCases;
use PHPUnit\TestFixture\PropertyBased\NameResolution\ClassThatUsesTrait;
use PHPUnit\TestFixture\PropertyBased\NameResolution\ClassWithImports;
use PHPUnit\TestFixture\PropertyBased\NameResolution\First\ClassInFirstNamespace;
use PHPUnit\TestFixture\PropertyBased\NameResolution\ParentClass;
use PHPUnit\TestFixture\PropertyBased\NameResolution\Second\ClassInSecondNamespace;
use PropertyBasedNameResolutionClassInGlobalNamespace;
use ReflectionMethod;

#[CoversClass(NameScope::class)]
#[CoversClass(NameContexts::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class NameScopeTest extends TestCase
{
    /**
     * @return array<string, array{class-string, non-empty-string, non-empty-string}>
     */
    public static function nameProvider(): array
    {
        return [
            'imported class'                                 => [Currency::class, ClassWithImports::class, 'Currency'],
            'imported class with alias'                      => [Currency::class, ClassWithImports::class, 'Money'],
            'class imported with group use'                  => [Currency::class, ClassWithImports::class, 'GroupedCurrency'],
            'class imported with group use without alias'    => [EnumerationWithoutCases::class, ClassWithImports::class, 'EnumerationWithoutCases'],
            'class in the global namespace that is imported' => [DateTimeImmutable::class, ClassWithImports::class, 'DateTimeImmutable'],
            'class in the same namespace'                    => ['PHPUnit\TestFixture\PropertyBased\NameResolution\Other', ClassWithImports::class, 'Other'],
            'class relative to the namespace'                => ['PHPUnit\TestFixture\PropertyBased\NameResolution\Sub\Other', ClassWithImports::class, 'Sub\Other'],
            'class relative to an import'                    => ['PHPUnit\TestFixture\PropertyBased\Currency\Nested', ClassWithImports::class, 'Currency\Nested'],
            'class name that is not affected by import case' => [Currency::class, ClassWithImports::class, 'money'],
            'fully qualified class name'                     => ['App\Money', ClassWithImports::class, '\App\Money'],
            'class imported after the class'                 => ['PHPUnit\TestFixture\PropertyBased\NameResolution\ImportedAfterTheClass', ClassWithImports::class, 'ImportedAfterTheClass'],
            'self'                                           => [ClassWithImports::class, ClassWithImports::class, 'self'],
            'static'                                         => [ClassWithImports::class, ClassWithImports::class, 'static'],
            'parent'                                         => [ParentClass::class, ClassWithImports::class, 'parent'],
            'import in the first of two braced namespaces'   => [Currency::class, ClassInFirstNamespace::class, 'Imported'],
            'import in the second of two braced namespaces'  => [EnumerationWithoutCases::class, ClassInSecondNamespace::class, 'Imported'],
            'class in the global namespace'                  => ['Other', PropertyBasedNameResolutionClassInGlobalNamespace::class, 'Other'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, class-string, non-empty-string}>
     */
    public static function shortNameProvider(): array
    {
        return [
            'shortest alias of an imported class' => ['Money', ClassWithImports::class, Currency::class],
            'imported class'                      => ['EnumerationWithoutCases', ClassWithImports::class, EnumerationWithoutCases::class],
            'class in the same namespace'         => ['ParentClass', ClassWithImports::class, ParentClass::class],
            'class that is not imported'          => ['\App\Money', ClassWithImports::class, 'App\Money'],
            'class with leading backslash'        => ['Money', ClassWithImports::class, '\\' . Currency::class],
            'class in the global namespace'       => ['Other', PropertyBasedNameResolutionClassInGlobalNamespace::class, 'Other'],
        ];
    }

    public function testResolvesClassNamesAtACallSiteAgainstTheNamespaceAndTheImportsThatApplyThere(): void
    {
        $method = new ReflectionMethod(ClassWithImports::class, 'method');
        $scope  = NameScope::forCallSite($method->getFileName(), $method->getStartLine(), ClassWithImports::class);

        $this->assertSame(Currency::class, $scope->resolve('Money'));
        $this->assertSame(ClassWithImports::class, $scope->resolve('self'));
        $this->assertSame(ParentClass::class, $scope->resolve('parent'));
    }

    public function testDoesNotReadTheFileUntilANameIsResolvedAgainstItsNamespaceAndImports(): void
    {
        $file  = sys_get_temp_dir() . '/' . uniqid('NameScopeTest_') . '.php';
        $scope = NameScope::forCallSite($file, 3, null);

        file_put_contents($file, "<?php\nnamespace App;\nfinal class Example {}\n");

        try {
            $this->assertSame('App\\Money', $scope->resolve('Money'));
        } finally {
            unlink($file);
        }
    }

    public function testSelfIsNotResolvedAtACallSiteOutsideOfAClass(): void
    {
        $this->assertSame('self', NameScope::forCallSite(false, false, null)->resolve('self'));
    }

    /**
     * @param class-string     $expected
     * @param class-string     $className
     * @param non-empty-string $name
     */
    #[DataProvider('nameProvider')]
    public function testResolvesClassNamesAgainstTheNamespaceAndTheImportsThatApplyToTheMethod(string $expected, string $className, string $name): void
    {
        $this->assertSame($expected, NameScope::forMethod(new ReflectionMethod($className, 'method'))->resolve($name));
    }

    public function testResolvesClassNamesInAMethodDeclaredInATraitAgainstTheNamespaceAndTheImportsOfTheTrait(): void
    {
        $scope = NameScope::forMethod(new ReflectionMethod(ClassThatUsesTrait::class, 'methodFromTrait'));

        $this->assertSame(Currency::class, $scope->resolve('TraitCurrency'));
        $this->assertSame('PHPUnit\TestFixture\PropertyBased\NameResolution\Traits\Other', $scope->resolve('Other'));
        $this->assertSame(ClassThatUsesTrait::class, $scope->resolve('self'));
    }

    public function testParentIsNotResolvedWhenTheClassHasNoParent(): void
    {
        $this->assertSame('parent', NameScope::forMethod(new ReflectionMethod(ClassInFirstNamespace::class, 'method'))->resolve('parent'));
    }

    public function testResolvesClassNamesInAMethodOfAnInternalClassAgainstTheGlobalNamespace(): void
    {
        $scope = NameScope::forMethod(new ReflectionMethod(DateTimeImmutable::class, '__construct'));

        $this->assertSame('Other', $scope->resolve('Other'));
        $this->assertSame('App\Money', $scope->shortName('App\Money'));
    }

    /**
     * @param non-empty-string $expected
     * @param class-string     $className
     * @param non-empty-string $classNameToShorten
     */
    #[DataProvider('shortNameProvider')]
    public function testReturnsTheShortestNameUnderWhichAClassCanBeReferredTo(string $expected, string $className, string $classNameToShorten): void
    {
        $this->assertSame($expected, NameScope::forMethod(new ReflectionMethod($className, 'method'))->shortName($classNameToShorten));
    }
}
