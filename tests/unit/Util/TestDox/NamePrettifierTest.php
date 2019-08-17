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

use PHPUnit\Framework\TestCase;

/**
 * @group testdox
 * @small
 */
final class NamePrettifierTest extends TestCase
{
    /**
     * @var NamePrettifier
     */
    private $namePrettifier;

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
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('FooTest'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('TestFoo'));
        $this->assertEquals('Foo', $this->namePrettifier->prettifyTestClass('TestFooTest'));
        $this->assertEquals('Foo (Test\Foo)', $this->namePrettifier->prettifyTestClass('Test\FooTest'));
        $this->assertEquals('Foo (Tests\Foo)', $this->namePrettifier->prettifyTestClass('Tests\FooTest'));
    }

    public function testTestNameIsConvertedToASentence(): void
    {
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('testThisIsATest'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('testThisIsATest2'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('this_is_a_test'));
        $this->assertEquals('This is a test', $this->namePrettifier->prettifyTestMethod('test_this_is_a_test'));
        $this->assertEquals('Foo for bar is 0', $this->namePrettifier->prettifyTestMethod('testFooForBarIs0'));
        $this->assertEquals('Foo for baz is 1', $this->namePrettifier->prettifyTestMethod('testFooForBazIs1'));
        $this->assertEquals('This has a 123 in its name', $this->namePrettifier->prettifyTestMethod('testThisHasA123InItsName'));
        $this->assertEquals('', $this->namePrettifier->prettifyTestMethod('test'));
    }

    /**
     * @ticket 224
     */
    public function testTestNameIsNotGroupedWhenNotInSequence(): void
    {
        $this->assertEquals('Sets redirect header on 301', $this->namePrettifier->prettifyTestMethod('testSetsRedirectHeaderOn301'));
        $this->assertEquals('Sets redirect header on 302', $this->namePrettifier->prettifyTestMethod('testSetsRedirectHeaderOn302'));
    }

    public function testPhpDoxIgnoreDataKeys(): void
    {
        $test = new class extends TestCase {
            public function __construct()
            {
                parent::__construct('testAddition', [
                    'augend' => 1,
                    'addend' => 2,
                    'result' => 3,
                ]);
            }

            public function testAddition(int $augend, int $addend, int $result): void
            {
            }

            public function getAnnotations(): array
            {
                return [
                    'method' => [
                        'testdox' => ['$augend + $addend = $result'],
                    ],
                ];
            }
        };

        $this->assertEquals('1 + 2 = 3', $this->namePrettifier->prettifyTestCase($test));
    }

    public function testPhpDoxUsesDefaultValue(): void
    {
        $test = new class extends TestCase {
            public function __construct()
            {
                parent::__construct('testAddition', []);
            }

            public function testAddition(int $augend = 1, int $addend = 2, int $result = 3): void
            {
            }

            public function getAnnotations(): array
            {
                return [
                    'method' => [
                        'testdox' => ['$augend + $addend = $result'],
                    ],
                ];
            }
        };

        $this->assertEquals('1 + 2 = 3', $this->namePrettifier->prettifyTestCase($test));
    }

    public function testPhpDoxArgumentExporting(): void
    {
        $test = new class extends TestCase {
            public function __construct()
            {
                parent::__construct('testExport', [
                    'int'      => 1234,
                    'strInt'   => '1234',
                    'float'    => 2.123,
                    'strFloat' => '2.123',
                    'string'   => 'foo',
                    'bool'     => true,
                    'null'     => null,
                ]);
            }

            public function testExport($int, $strInt, $float, $strFloat, $string, $bool, $null): void
            {
            }

            public function getAnnotations(): array
            {
                return [
                    'method' => [
                        'testdox' => ['$int, $strInt, $float, $strFloat, $string, $bool, $null'],
                    ],
                ];
            }
        };

        $this->assertEquals('1234, 1234, 2.123, 2.123, foo, true, NULL', $this->namePrettifier->prettifyTestCase($test));
    }

    public function testPhpDoxReplacesLongerVariablesFirst(): void
    {
        $test = new class extends TestCase {
            public function __construct()
            {
                parent::__construct('testFoo', []);
            }

            public function testFoo(int $a = 1, int $ab = 2, int $abc = 3): void
            {
            }

            public function getAnnotations(): array
            {
                return [
                    'method' => [
                        'testdox' => ['$a, "$a", $a$ab, $abc, $abcd, $ab'],
                    ],
                ];
            }
        };

        $this->assertEquals('1, "1", 12, 3, $abcd, 2', $this->namePrettifier->prettifyTestCase($test));
    }
}
