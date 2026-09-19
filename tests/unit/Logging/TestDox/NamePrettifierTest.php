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

use DateTimeImmutable;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\BackedEnumeration;
use PHPUnit\TestFixture\Enumeration;
use PHPUnit\TestFixture\TestDox\TestDoxAttributeOnTestClassTest;
use PHPUnit\TestFixture\TestDoxFormatterErrorTest;
use PHPUnit\TestFixture\TestDoxTest;
use stdClass;

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
            [
                'Foo',
                'FooTest',
            ],
            [
                'Test Foo',
                'TestFoo',
            ],
            [
                'Tests Foo',
                'TestsFoo',
            ],
            [
                'Test Foo',
                'TestFooTest',
            ],
            [
                'Foo (Test\Foo)',
                'Test\FooTest',
            ],
            [
                'Foo (Tests\Foo)',
                'Tests\FooTest',
            ],
            [
                'Test',
                'TestTest',
            ],
            [
                'Système Testé',
                'SystèmeTestéTest',
            ],
            [
                'Expression Évaluée',
                'ExpressionÉvaluéeTest',
            ],
            [
                'Custom Title',
                TestDoxAttributeOnTestClassTest::class,
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function methodNameProvider(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '',
                'test',
            ],
            [
                'This is a test',
                'this_is_a_test',
            ],
            [
                'This is a test',
                'test_this_is_a_test',
            ],
            [
                'Foo for bar is 0',
                'testFooForBarIs0',
            ],
            [
                'Foo for baz is 1',
                'testFooForBazIs1',
            ],
            [
                'This has a 123 in its name',
                'testThisHasA123InItsName',
            ],
            [
                'Sets redirect header on 301',
                'testSetsRedirectHeaderOn301',
            ],
            [
                'Sets redirect header on 302',
                'testSetsRedirectHeaderOn302',
            ],
            [
                '100 users',
                'test100Users',
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: TestCase, 2: bool}>
     */
    public static function objectProvider(): array
    {
        $object = new class
        {
            public function __toString(): string
            {
                return 'object as string';
            }
        };

        $data = [['string'], true, 0.0, 1, 'string', $object, new stdClass, Enumeration::Test, BackedEnumeration::Test, null, ''];

        $testWithDataWithIntegerKey = new TestDoxTest('testTwo');
        $testWithDataWithIntegerKey->setData(0, $data);

        $testWithDataWithStringKey = new TestDoxTest('testTwo');
        $testWithDataWithStringKey->setData('a', $data);

        $testWithDataAndTestDoxPlaceholders = new TestDoxTest('testFour');
        $testWithDataAndTestDoxPlaceholders->setData('a', $data);

        $testWithTestDoxFormatter = new TestDoxTest('testFive');
        $testWithTestDoxFormatter->setData(0, [new DateTimeImmutable('2025-07-09')]);

        $testWithFewerDataValuesThanParameters = new TestDoxTest('testSix');
        $testWithFewerDataValuesThanParameters->setData(0, ['value']);

        return [
            [
                'One',
                new TestDoxTest('testOne'),
                false,
            ],
            [
                'Two with data set #0',
                $testWithDataWithIntegerKey,
                false,
            ],
            [
                'Two with data set "a"',
                $testWithDataWithStringKey,
                false,
            ],
            [
                'This is a custom test description',
                new TestDoxTest('testThree'),
                false,
            ],
            [
                'This is a custom test description with placeholders array true 0.0 1 string object as string stdClass Test test null \'\' default',
                $testWithDataAndTestDoxPlaceholders,
                false,
            ],
            [
                "This is a custom test description with placeholders \e[36marray\e[0m \e[36mtrue\e[0m \e[36m0.0\e[0m \e[36m1\e[0m \e[36mstring\e[0m \e[36mobject\e[2m·\e[22mas\e[2m·\e[22mstring\e[0m \e[36mstdClass\e[0m \e[36mTest\e[0m \e[36mtest\e[0m \e[36mnull\e[0m \e[36;2;4mempty\e[0m \e[36mdefault\e[0m",
                $testWithDataAndTestDoxPlaceholders,
                true,
            ],
            [
                'This is a custom description: 2025-07-09',
                $testWithTestDoxFormatter,
                false,
            ],
            [
                'Value of a is value, value of b is null',
                $testWithFewerDataValuesThanParameters,
                false,
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string}>
     */
    public static function brokenFormatterProvider(): array
    {
        $className = TestDoxFormatterErrorTest::class;

        return [
            [
                'testWithFormatterThatDoesNotExist',
                'With formatter that does not exist',
                'Method ' . $className . '::formatterThatDoesNotExist() cannot be used as a TestDox formatter because it does not exist',
            ],
            [
                'testWithFormatterThatIsNotPublic',
                'With formatter that is not public',
                'Method ' . $className . '::formatterThatIsNotPublic() cannot be used as a TestDox formatter because it is not public',
            ],
            [
                'testWithFormatterThatIsNotStatic',
                'With formatter that is not static',
                'Method ' . $className . '::formatterThatIsNotStatic() cannot be used as a TestDox formatter because it is not static',
            ],
        ];
    }

    /**
     * @param non-empty-string $expected
     * @param non-empty-string $className
     */
    #[DataProvider('classNameProvider')]
    public function testNameOfTestClassCanBePrettified(string $expected, string $className): void
    {
        $this->assertSame($expected, new NamePrettifier($this->createStub(Emitter::class))->prettifyTestClassName($className));
    }

    /**
     * @param non-empty-string $expected
     * @param non-empty-string $methodName
     */
    #[DataProvider('methodNameProvider')]
    public function testNameOfTestMethodCanBePrettified(string $expected, string $methodName): void
    {
        $this->assertSame($expected, new NamePrettifier($this->createStub(Emitter::class))->prettifyTestMethodName($methodName));
    }

    /**
     * @param non-empty-string $expected
     */
    #[DataProvider('objectProvider')]
    public function test_TestCase_can_be_prettified(string $expected, TestCase $testCase, bool $colorize): void
    {
        $this->assertSame($expected, new NamePrettifier($this->createStub(Emitter::class))->prettifyTestCase($testCase, $colorize));
    }

    /**
     * @param non-empty-string $methodName
     * @param non-empty-string $expected
     * @param non-empty-string $message
     */
    #[DataProvider('brokenFormatterProvider')]
    public function testEmitsErrorWhenFormatterCannotBeUsed(string $methodName, string $expected, string $message): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testTriggeredPhpunitError')
            ->with($this->isInstanceOf(TestMethod::class), $message)
            ->seal();

        $namePrettifier = new NamePrettifier($emitter);
        $test           = new TestDoxFormatterErrorTest($methodName);

        $this->assertSame($expected, $namePrettifier->prettifyTestCase($test, false));
        $this->assertSame($expected, $namePrettifier->prettifyTestCase($test, false));
    }

    public function testEmitsErrorWhenFormatterThrows(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testTriggeredPhpunitError')
            ->with(
                $this->isInstanceOf(TestMethod::class),
                $this->stringStartsWith('TestDox formatter ' . TestDoxFormatterErrorTest::class . '::formatterThatThrows() triggered an error: message'),
            )
            ->seal();

        $namePrettifier = new NamePrettifier($emitter);
        $test           = new TestDoxFormatterErrorTest('testWithFormatterThatThrows');

        $this->assertSame('With formatter that throws', $namePrettifier->prettifyTestCase($test, false));
    }

    public function testStripsNumericSuffixFromTestMethodNameWhenTestMethodNameWithoutThatSuffixWasPreviouslyProcessed(): void
    {
        $namePrettifier = new NamePrettifier($this->createStub(Emitter::class));

        $this->assertSame('This is a test', $namePrettifier->prettifyTestMethodName('testThisIsATest'));
        $this->assertSame('This is a test', $namePrettifier->prettifyTestMethodName('testThisIsATest2'));
    }
}
