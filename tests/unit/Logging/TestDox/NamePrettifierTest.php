<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestDox\TestDoxAttributeOnTestClassTest;

#[CoversClass(NamePrettifier::class)]
#[Group('testdox')]
#[Small]
final class NamePrettifierTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function classNameProvider(): array
    {
        return [
            ['Foo', 'FooTest'],
            ['Foo', 'TestFoo'],
            ['Foo', 'TestsFoo'],
            ['Foo', 'TestFooTest'],
            ['Foo (Test\Foo)', 'Test\FooTest'],
            ['Foo (Tests\Foo)', 'Tests\FooTest'],
            ['Unnamed Tests', 'TestTest'],
            ['Système Testé', 'SystèmeTestéTest'],
            ['Expression Évaluée', 'ExpressionÉvaluéeTest'],
            ['Custom Title', TestDoxAttributeOnTestClassTest::class],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function methodNameProvider(): array
    {
        return [
            ['', ''],
            ['', 'test'],
            ['This is a test', 'testThisIsATest'],
            ['This is a test', 'testThisIsATest2'],
            ['This is a test', 'this_is_a_test'],
            ['This is a test', 'test_this_is_a_test'],
            ['Foo for bar is 0', 'testFooForBarIs0'],
            ['Foo for baz is 1', 'testFooForBazIs1'],
            ['This has a 123 in its name', 'testThisHasA123InItsName'],
            ['Sets redirect header on 301', 'testSetsRedirectHeaderOn301'],
            ['Sets redirect header on 302', 'testSetsRedirectHeaderOn302'],
        ];
    }

    /**
     * @param non-empty-string $expected
     * @param non-empty-string $className
     */
    #[DataProvider('classNameProvider')]
    public function testNameOfTestClassCanBePrettified(string $expected, string $className): void
    {
        $this->assertSame($expected, (new NamePrettifier)->prettifyTestClassName($className));
    }

    /**
     * @param non-empty-string $expected
     * @param non-empty-string $methodName
     */
    #[DataProvider('methodNameProvider')]
    public function testNameOfTestMethodCanBePrettified(string $expected, string $methodName): void
    {
        $this->assertSame($expected, (new NamePrettifier)->prettifyTestMethodName($methodName));
    }
}
