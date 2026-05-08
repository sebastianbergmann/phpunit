<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact\ProgressPrinter;

use function hrtime;
use Closure;
use Exception;
use LogicException;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodFailed;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TextUI\Output\Printer;
use RuntimeException;

#[CoversClass(ProgressPrinter::class)]
#[Medium]
final class ProgressPrinterTest extends TestCase
{
    /**
     * @return array<string,array{0: string, 1: Closure(ProgressPrinter): void}>
     */
    public static function provider(): array
    {
        return [
            'errored test' => [
                'errored_test.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testErrored(
                        new Errored(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new Exception('message')),
                        ),
                    );
                },
            ],

            'errored test with previous' => [
                'errored_test_with_previous.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testErrored(
                        new Errored(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new RuntimeException('outer', 0, new LogicException('inner'))),
                        ),
                    );
                },
            ],

            'failed test' => [
                'failed_test.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testFailed(
                        new Failed(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                            null,
                        ),
                    );
                },
            ],

            'failed test with data provider' => [
                'failed_test_with_data_provider.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testFailed(
                        new Failed(
                            self::telemetryInfo(),
                            self::testMethodWithDataProvider(),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that 1 matches expected 2.')),
                            null,
                        ),
                    );
                },
            ],

            'before first test method errored' => [
                'before_first_test_method_errored.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->beforeFirstTestMethodErrored(
                        new BeforeFirstTestMethodErrored(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'setUpBeforeClass'),
                            ThrowableBuilder::from(new Exception('message')),
                        ),
                    );
                },
            ],

            'before first test method failed' => [
                'before_first_test_method_failed.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->beforeFirstTestMethodFailed(
                        new BeforeFirstTestMethodFailed(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'setUpBeforeClass'),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                        ),
                    );
                },
            ],

            'after last test method errored' => [
                'after_last_test_method_errored.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->afterLastTestMethodErrored(
                        new AfterLastTestMethodErrored(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'tearDownAfterClass'),
                            ThrowableBuilder::from(new Exception('message')),
                        ),
                    );
                },
            ],

            'after last test method failed' => [
                'after_last_test_method_failed.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->afterLastTestMethodFailed(
                        new AfterLastTestMethodFailed(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'tearDownAfterClass'),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                        ),
                    );
                },
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testPrintsExpectedOutputForEvent(string $expectationFile, Closure $trigger): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade);

        $trigger($progressPrinter);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringMatchesFormatFile(
            __DIR__ . '/expectations/progress/' . $expectationFile,
            $printer->buffer(),
        );
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

    private static function testMethod(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    private static function testMethodWithDataProvider(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([
                DataFromDataProvider::from(
                    'negative numbers',
                    'a]',
                    '#2 (negative numbers)',
                ),
            ]),
        );
    }

    private static function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
        );
    }
}
