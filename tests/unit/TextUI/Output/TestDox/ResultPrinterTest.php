<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\TestDox;

use const PHP_EOL;
use function hrtime;
use function substr_count;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Logging\TestDox\TestResult as TestDoxTestResult;
use PHPUnit\Logging\TestDox\TestResultCollection;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\Printer;

#[CoversClass(ResultPrinter::class)]
#[Small]
#[Group('textui')]
final class ResultPrinterTest extends TestCase
{
    /**
     * @return array<string, array{TestStatus, string}>
     */
    public static function statusProvider(): array
    {
        return [
            'error'      => [TestStatus::error('message'), '✘'],
            'failure'    => [TestStatus::failure('message'), '✘'],
            'skipped'    => [TestStatus::skipped('message'), '↩'],
            'incomplete' => [TestStatus::incomplete('message'), '∅'],
            'notice'     => [TestStatus::notice('message'), '⚠'],
            'unknown'    => [TestStatus::unknown(), '?'],
        ];
    }

    #[DataProvider('statusProvider')]
    public function testPrintsColorizedResultForEveryStatus(TestStatus $status, string $symbol): void
    {
        $printer = $this->printer();

        $this->resultPrinter($printer, true)->print(
            $this->testResult(),
            [
                'FooTest' => TestResultCollection::fromArray([
                    new TestDoxTestResult(
                        $this->testMethod(),
                        $status,
                        $this->throwable(),
                    ),
                ]),
            ],
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringContainsString($symbol, $printer->buffer());
    }

    public function testDoesNotPrintClassesWithoutIssuesInSummary(): void
    {
        $printer = $this->printer();

        $this->resultPrinter($printer, false, true)->print(
            $this->testResult(),
            [
                'FooTest' => TestResultCollection::fromArray([
                    new TestDoxTestResult(
                        $this->testMethod(),
                        TestStatus::success(),
                        null,
                    ),
                ]),
            ],
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $buffer = $printer->buffer();

        $this->assertStringContainsString('Summary of tests with errors, failures, or issues:', $buffer);
        $this->assertSame(1, substr_count($buffer, 'Foo'));
    }

    public function testDoesNotPrintClassWithoutTests(): void
    {
        $printer = $this->printer();

        $this->resultPrinter($printer)->print(
            $this->testResult(),
            [
                'FooTest' => TestResultCollection::fromArray([]),
            ],
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame('', $printer->buffer());
    }

    public function testPrintsStackTraceWithoutMessage(): void
    {
        $printer = $this->printer();

        $this->resultPrinter($printer, true)->print(
            $this->testResult(),
            [
                'FooTest' => TestResultCollection::fromArray([
                    new TestDoxTestResult(
                        $this->testMethod(),
                        TestStatus::failure(),
                        new Throwable(
                            'PHPUnit\Framework\ExpectationFailedException',
                            '',
                            '',
                            '/path/to/FooTest.php:1' . PHP_EOL . '/path/to/BarTest.php:2',
                            null,
                        ),
                    ),
                ]),
            ],
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringContainsString('FooTest.php', $printer->buffer());
    }

    public function testPrintsMessageWhenTerminalIsNarrow(): void
    {
        $printer = $this->printer();

        $this->resultPrinter($printer, true, false, 1)->print(
            $this->testResult(),
            [
                'FooTest' => TestResultCollection::fromArray([
                    new TestDoxTestResult(
                        $this->testMethod(),
                        TestStatus::failure(),
                        $this->throwable(),
                    ),
                ]),
            ],
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringContainsString('message', $printer->buffer());
    }

    public function testPrintsErrorsTriggeredAfterLastTestMethod(): void
    {
        $printer = $this->printer();

        $this->resultPrinter($printer)->print(
            $this->testResult([$this->afterLastTestMethodErrored()]),
            [],
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringContainsString('tearDownAfterClass', $printer->buffer());
    }

    private function resultPrinter(Printer $printer, bool $colors = false, bool $printSummary = false, int $columns = 80): ResultPrinter
    {
        return new ResultPrinter($printer, $colors, $columns, $printSummary);
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

    private function throwable(): Throwable
    {
        return new Throwable(
            'RuntimeException',
            'message',
            'RuntimeException: message',
            '/path/to/FooTest.php:1',
            null,
        );
    }

    private function testMethod(): TestMethod
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

    private function afterLastTestMethodErrored(): AfterLastTestMethodErrored
    {
        return new AfterLastTestMethodErrored(
            $this->telemetryInfo(),
            'FooTest',
            new ClassMethod('FooTest', 'tearDownAfterClass'),
            $this->throwable(),
        );
    }

    /**
     * @param list<AfterLastTestMethodErrored> $testErroredEvents
     */
    private function testResult(array $testErroredEvents = []): TestResult
    {
        return new TestResult(
            1,
            1,
            1,
            $testErroredEvents,
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            0,
            [
                'self'     => 0,
                'direct'   => 0,
                'indirect' => 0,
                'unknown'  => 0,
            ],
        );
    }

    private function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
        );
    }
}
