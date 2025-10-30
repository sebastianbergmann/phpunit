<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Default;

use const PHP_OS_FAMILY;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\TextUI\Output\SummaryPrinter;

#[CoversClass(SummaryPrinter::class)]
#[Medium]
final class SummaryPrinterTest extends TestCase
{
    #[DataProviderExternal(ResultPrinterTest::class, 'provider', false)]
    public function testPrintsExpectedOutputForTestResultObject(string $expectationFile, TestResult $result): void
    {
        $printer = $this->printer();

        $summaryPrinter = new SummaryPrinter($printer, false);

        $summaryPrinter->print($result);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringMatchesFormatFile(
            __DIR__ . '/expectations/summary/' . $expectationFile,
            $printer->buffer(),
        );
    }

    #[DataProviderExternal(ResultPrinterTest::class, 'provider', false)]
    public function testPrintsExpectedColouredOutputForTestResultObject(string $expectationFile, TestResult $result): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $printer = $this->printer();

        $summaryPrinter = new SummaryPrinter($printer, true);

        $summaryPrinter->print($result);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringMatchesFormatFile(
            __DIR__ . '/expectations/summary-coloured/' . $expectationFile,
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
}
