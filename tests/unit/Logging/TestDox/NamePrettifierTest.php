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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestDox\TestDoxAttributeOnTestClassTest;

#[CoversClass(NamePrettifier::class)]
#[Group('testdox')]
#[Small]
final class NamePrettifierTest extends TestCase
{
    public function testNameOfTestClassCanBePrettified(): void
    {
        $this->assertSame('Foo', (new NamePrettifier)->prettifyTestClassName('FooTest'));
        $this->assertSame('Foo', (new NamePrettifier)->prettifyTestClassName('TestFoo'));
        $this->assertSame('Foo', (new NamePrettifier)->prettifyTestClassName('TestsFoo'));
        $this->assertSame('Foo', (new NamePrettifier)->prettifyTestClassName('TestFooTest'));
        $this->assertSame('Foo (Test\Foo)', (new NamePrettifier)->prettifyTestClassName('Test\FooTest'));
        $this->assertSame('Foo (Tests\Foo)', (new NamePrettifier)->prettifyTestClassName('Tests\FooTest'));
        $this->assertSame('Unnamed Tests', (new NamePrettifier)->prettifyTestClassName('TestTest'));
        $this->assertSame('Système Testé', (new NamePrettifier)->prettifyTestClassName('SystèmeTestéTest'));
        $this->assertSame('Expression Évaluée', (new NamePrettifier)->prettifyTestClassName('ExpressionÉvaluéeTest'));
        $this->assertSame('Custom Title', (new NamePrettifier)->prettifyTestClassName(TestDoxAttributeOnTestClassTest::class));
    }

    public function testNameOfTestMethodCanBePrettified(): void
    {
        $this->assertSame('', (new NamePrettifier)->prettifyTestMethodName(''));
        $this->assertSame('', (new NamePrettifier)->prettifyTestMethodName('test'));
        $this->assertSame('This is a test', (new NamePrettifier)->prettifyTestMethodName('testThisIsATest'));
        $this->assertSame('This is a test', (new NamePrettifier)->prettifyTestMethodName('testThisIsATest2'));
        $this->assertSame('This is a test', (new NamePrettifier)->prettifyTestMethodName('this_is_a_test'));
        $this->assertSame('This is a test', (new NamePrettifier)->prettifyTestMethodName('test_this_is_a_test'));
        $this->assertSame('Foo for bar is 0', (new NamePrettifier)->prettifyTestMethodName('testFooForBarIs0'));
        $this->assertSame('Foo for baz is 1', (new NamePrettifier)->prettifyTestMethodName('testFooForBazIs1'));
        $this->assertSame('This has a 123 in its name', (new NamePrettifier)->prettifyTestMethodName('testThisHasA123InItsName'));
        $this->assertSame('Sets redirect header on 301', (new NamePrettifier)->prettifyTestMethodName('testSetsRedirectHeaderOn301'));
        $this->assertSame('Sets redirect header on 302', (new NamePrettifier)->prettifyTestMethodName('testSetsRedirectHeaderOn302'));
    }
}
