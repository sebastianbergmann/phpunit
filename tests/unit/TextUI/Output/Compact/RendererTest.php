<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact;

use const PHP_EOL;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TextUI\Output\Printer;

#[CoversClass(Renderer::class)]
#[Small]
#[Group('textui')]
final class RendererTest extends TestCase
{
    public function testRendersNameOfTestMethodThatDoesNotUseDataProvider(): void
    {
        $this->assertSame(
            'FooTest::testBar',
            new Renderer($this->printer())->nameOfTest($this->testMethod(false)),
        );
    }

    public function testRendersNameOfTestMethodThatUsesDataProvider(): void
    {
        $this->assertSame(
            'FooTest::testBar#2',
            new Renderer($this->printer())->nameOfTest($this->testMethod(true)),
        );
    }

    public function testRendersNameOfTestThatIsNotATestMethod(): void
    {
        $this->assertSame(
            'FooTest.phpt',
            new Renderer($this->printer())->nameOfTest(new Phpt('FooTest.phpt')),
        );
    }

    public function testRendersThrowableWithoutPreviousThrowable(): void
    {
        $printer = $this->printer();

        new Renderer($printer)->printThrowable(
            new Throwable(
                'RuntimeException',
                'outer',
                'RuntimeException: outer',
                'FooTest.php:1',
                null,
            ),
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame(
            'RuntimeException: outer' . PHP_EOL . PHP_EOL . 'FooTest.php:1' . PHP_EOL,
            $printer->buffer(),
        );
    }

    public function testRendersThrowableWithPreviousThrowable(): void
    {
        $printer = $this->printer();

        new Renderer($printer)->printThrowable(
            new Throwable(
                'RuntimeException',
                'outer',
                'RuntimeException: outer',
                'FooTest.php:1',
                new Throwable(
                    'LogicException',
                    'inner',
                    'LogicException: inner',
                    'FooTest.php:2',
                    null,
                ),
            ),
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame(
            'RuntimeException: outer' . PHP_EOL .
            PHP_EOL . 'FooTest.php:1' . PHP_EOL .
            'Caused by' . PHP_EOL .
            'LogicException: inner' . PHP_EOL .
            PHP_EOL . 'FooTest.php:2' . PHP_EOL,
            $printer->buffer(),
        );
    }

    public function testDoesNotRenderEmptyStackTrace(): void
    {
        $printer = $this->printer();

        new Renderer($printer)->printStackTrace('   ');

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame('', $printer->buffer());
    }

    private function printer(): Printer
    {
        return new class implements Printer
        {
            private string $buffer = '';

            public function print(string $buffer): void
            {
                $this->buffer .= $buffer;
            }

            public function flush(): void
            {
            }

            public function buffer(): string
            {
                return $this->buffer;
            }
        };
    }

    private function testMethod(bool $withDataProvider): TestMethod
    {
        if ($withDataProvider) {
            $testData = TestDataCollection::fromArray([
                DataFromDataProvider::from(
                    'negative numbers',
                    'a]',
                    '#2',
                ),
            ]);
        } else {
            $testData = TestDataCollection::fromArray([]);
        }

        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            $testData,
        );
    }
}
