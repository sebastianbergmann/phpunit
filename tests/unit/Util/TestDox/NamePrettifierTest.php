<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Logging\TestDox\NamePrettifier;

#[CoversClass(NamePrettifier::class)]
#[Group('testdox')]
#[Small]
final class NamePrettifierTest extends TestCase
{
    private ?NamePrettifier $namePrettifier;

    protected function setUp(): void
    {
        $this->namePrettifier = new NamePrettifier;
    }

    protected function tearDown(): void
    {
        $this->namePrettifier = null;
    }

    public function testTitleHasSensibleDefaults(): void
    {
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClassName('FooTest'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClassName('TestFoo'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClassName('TestFooTest'));
        $this->assertEquals('Foo (Test\Foo)', $this->namePrettifier->prettifyTestClassName('Test\FooTest'));
        $this->assertEquals('Foo (Tests\Foo)', $this->namePrettifier->prettifyTestClassName('Tests\FooTest'));
        $this->assertEquals('Unnamed Tests', $this->namePrettifier->prettifyTestClassName('TestTest'));
        $this->assertEquals('Système Testé', $this->namePrettifier->prettifyTestClassName('SystèmeTestéTest'));
        $this->assertEquals('Expression Évaluée', $this->namePrettifier->prettifyTestClassName('ExpressionÉvaluéeTest'));
    }

    public function testTestNameIsConvertedToASentence(): void
    {
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethodName('testThisIsATest'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethodName('testThisIsATest2'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethodName('this_is_a_test'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethodName('test_this_is_a_test'));
        $this->assertEquals('Foo for bar is 0', $this->namePrettifier->prettifyTestMethodName('testFooForBarIs0'));
        $this->assertEquals('Foo for baz is 1', $this->namePrettifier->prettifyTestMethodName('testFooForBazIs1'));
        $this->assertEquals('This has a 123 in its name', $this->namePrettifier->prettifyTestMethodName('testThisHasA123InItsName'));
        $this->assertEquals('', $this->namePrettifier->prettifyTestMethodName('test'));
    }

    public function testTestNameIsNotGroupedWhenNotInSequence(): void
    {
        $this->assertEquals('Sets redirect header on 301', $this->namePrettifier->prettifyTestMethodName('testSetsRedirectHeaderOn301'));
        $this->assertEquals('Sets redirect header on 302', $this->namePrettifier->prettifyTestMethodName('testSetsRedirectHeaderOn302'));
    }
}
