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
use function array_map;
use function assert;
use function implode;
use function preg_split;
use function trim;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestData\NoDataSetFromDataProviderException;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Logging\TestDox\TestMethod;
use PHPUnit\Logging\TestDox\TestMethodCollection;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter
{
    private Printer $printer;
    private bool $colors;

    public function __construct(Printer $printer, bool $colors)
    {
        $this->printer = $printer;
        $this->colors  = $colors;
    }

    /**
     * @psalm-param array<string, TestMethodCollection> $tests
     */
    public function print(array $tests): void
    {
        foreach ($tests as $prettifiedClassName => $_tests) {
            $this->printPrettifiedClassName($prettifiedClassName);

            foreach ($_tests as $test) {
                $this->printTestResult($test);
            }

            $this->printer->print(PHP_EOL);
        }
    }

    public function flush(): void
    {
        $this->printer->flush();
    }

    /**
     * @psalm-param string $prettifiedClassName
     */
    private function printPrettifiedClassName(string $prettifiedClassName): void
    {
        $buffer = $prettifiedClassName;

        if ($this->colors) {
            $buffer = Color::colorizeTextBox('underlined', $buffer);
        }

        $this->printer->print($buffer . PHP_EOL);
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    private function printTestResult(TestMethod $test): void
    {
        $this->printTestResultHeader($test);
        $this->printTestResultBody($test);
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    private function printTestResultHeader(TestMethod $test): void
    {
        if ($this->colors) {
            $this->printer->print(
                Color::colorizeTextBox(
                    $this->colorFor($test->status()),
                    ' ' . $this->symbolFor($test->status()) . ' '
                )
            );
        } else {
            $this->printer->print(' ' . $this->symbolFor($test->status()) . ' ');
        }

        $this->printer->print($test->test()->prettifiedMethodName() . PHP_EOL);
    }

    private function printTestResultBody(TestMethod $test): void
    {
        if ($test->status()->isSuccess()) {
            return;
        }

        $throwable = $test->throwable();

        assert($throwable instanceof Throwable);

        $this->printer->print(
            $this->prefixLines(
                $this->prefixFor('default', $test->status()),
                PHP_EOL . $this->formatThrowable($throwable)
            )
        );

        $this->printer->print(PHP_EOL);
    }

    private function formatThrowable(Throwable $t): string
    {
        $message = trim($t->description());

        if ($message) {
            $message .= PHP_EOL . PHP_EOL;
        }

        return $message . $this->formatStackTrace($t->stackTrace());
    }

    private function formatStackTrace(string $stackTrace): string
    {
        if (!$this->colors) {
            return $stackTrace;
        }
    }

    private function prefixLines(string $prefix, string $message): string
    {
        return implode(
            PHP_EOL,
            array_map(
                static function (string $line) use ($prefix)
                {
                    return '   ' . $prefix . ($line ? ' ' . $line : '');
                },
                preg_split('/\r\n|\r|\n/', $message)
            )
        );
    }

    /**
     * @psalm-param 'default'|'start'|'message'|'diff'|'trace'|'last' $type
     */
    private function prefixFor(string $type, TestStatus $status): string
    {
        if (!$this->colors) {
            return '│';
        }

        return Color::colorize(
            $this->colorFor($status),
            match ($type) {
                'default' => '│',
                'start'   => '┐',
                'message' => '├',
                'diff'    => '┊',
                'trace'   => '╵',
                'last'    => '┴'
            }
        );
    }

    private function colorFor(TestStatus $status): string
    {
        if ($status->isSuccess()) {
            return 'fg-green';
        }

        if ($status->isError()) {
            return 'fg-yellow';
        }

        if ($status->isFailure()) {
            return 'fg-red';
        }

        if ($status->isSkipped()) {
            return 'fg-cyan';
        }

        if ($status->isRisky() || $status->isIncomplete() || $status->isWarning()) {
            return 'fg-yellow';
        }

        return 'fg-blue';
    }

    private function messageColorFor(TestStatus $status): ?string
    {
        if ($status->isSuccess()) {
            return null;
        }

        if ($status->isError()) {
            return 'bg-yellow,fg-black';
        }

        if ($status->isFailure()) {
            return 'bg-red,fg-white';
        }

        if ($status->isSkipped()) {
            return 'fg-cyan';
        }

        if ($status->isRisky() || $status->isIncomplete() || $status->isWarning()) {
            return 'fg-yellow';
        }

        return 'fg-white,bg-blue';
    }

    private function symbolFor(TestStatus $status): string
    {
        if ($status->isSuccess()) {
            return '✔';
        }

        if ($status->isError() || $status->isFailure()) {
            return '✘';
        }

        if ($status->isSkipped()) {
            return '↩';
        }

        if ($status->isRisky()) {
            return '☢';
        }

        if ($status->isIncomplete()) {
            return '∅';
        }

        if ($status->isWarning()) {
            return '⚠';
        }

        return '?';
    }
}
