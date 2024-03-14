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

#[CoversClass(NamePrettifier::class)]
#[Group('testdox')]
#[Small]
final class NamePrettifierTest extends TestCase
{
    public function testTitleHasSensibleDefaults(): void
    {
        $this->assertEquals('Foo', (new NamePrettifier)->prettifyTestClassName('FooTest'));
        $this->assertEquals('Foo', (new NamePrettifier)->prettifyTestClassName('TestFoo'));
        $this->assertEquals('Foo', (new NamePrettifier)->prettifyTestClassName('TestFooTest'));
        $this->assertEquals('Foo (Test\Foo)', (new NamePrettifier)->prettifyTestClassName('Test\FooTest'));
        $this->assertEquals('Foo (Tests\Foo)', (new NamePrettifier)->prettifyTestClassName('Tests\FooTest'));
        $this->assertEquals('Unnamed Tests', (new NamePrettifier)->prettifyTestClassName('TestTest'));
        $this->assertEquals('Système Testé', (new NamePrettifier)->prettifyTestClassName('SystèmeTestéTest'));
        $this->assertEquals('Expression Évaluée', (new NamePrettifier)->prettifyTestClassName('ExpressionÉvaluéeTest'));
    }

    public function testTestNameIsConvertedToASentence(): void
    {
        $this->assertEquals('', (new NamePrettifier)->prettifyTestMethodName(''));
        $this->assertEquals('This is a test', (new NamePrettifier)->prettifyTestMethodName('testThisIsATest'));
        $this->assertEquals('This is a test', (new NamePrettifier)->prettifyTestMethodName('testThisIsATest2'));
        $this->assertEquals('This is a test', (new NamePrettifier)->prettifyTestMethodName('this_is_a_test'));
        $this->assertEquals('This is a test', (new NamePrettifier)->prettifyTestMethodName('test_this_is_a_test'));
        $this->assertEquals('Foo for bar is 0', (new NamePrettifier)->prettifyTestMethodName('testFooForBarIs0'));
        $this->assertEquals('Foo for baz is 1', (new NamePrettifier)->prettifyTestMethodName('testFooForBazIs1'));
        $this->assertEquals('This has a 123 in its name', (new NamePrettifier)->prettifyTestMethodName('testThisHasA123InItsName'));
        $this->assertEquals('', (new NamePrettifier)->prettifyTestMethodName('test'));
    }

    public function testTestNameIsNotGroupedWhenNotInSequence(): void
    {
        $this->assertEquals('Sets redirect header on 301', (new NamePrettifier)->prettifyTestMethodName('testSetsRedirectHeaderOn301'));
        $this->assertEquals('Sets redirect header on 302', (new NamePrettifier)->prettifyTestMethodName('testSetsRedirectHeaderOn302'));
    }
}
