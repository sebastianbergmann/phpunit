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
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\NoDataSetFromDataProviderException;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Logging\TestDox\NamePrettifier;
use PHPUnit\Logging\TestDox\TestMethodCollection;
use PHPUnit\Util\Color;
use PHPUnit\Util\Printer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter
{
    /**
     * The default TestDox left margin for messages is a vertical line.
     */
    private const PREFIX_SIMPLE = [
        'default' => '│',
        'start'   => '│',
        'message' => '│',
        'diff'    => '│',
        'trace'   => '│',
        'last'    => '│',
    ];

    /**
     * Colored TestDox use box-drawing for a more textured map of the message.
     */
    private const PREFIX_DECORATED = [
        'default' => '│',
        'start'   => '┐',
        'message' => '├',
        'diff'    => '┊',
        'trace'   => '╵',
        'last'    => '┴',
    ];
    private Printer $printer;
    private bool $colors;
    private NamePrettifier $namePrettifier;

    public function __construct(Printer $printer, bool $colors)
    {
        $this->printer        = $printer;
        $this->colors         = $colors;
        $this->namePrettifier = new NamePrettifier;
    }

    /**
     * @psalm-param array<string, TestMethodCollection> $tests
     */
    public function print(array $tests): void
    {
        foreach ($tests as $prettifiedClassName => $_tests) {
            $this->printPrettifiedClassName($prettifiedClassName);

            foreach ($_tests as $test) {
                $this->printTestResult(
                    $test->test(),
                    $test->status(),
                );
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
    private function printTestResult(TestMethod $test, TestStatus $status): void
    {
        $this->printTestMethodPrefixHeader($test, $status);

        if ($status->isSuccess()) {
            return;
        }
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    private function printTestMethodPrefixHeader(TestMethod $test, TestStatus $status): void
    {
        $style = $this->style($status);

        if ($this->colors) {
            $this->printer->print(
                Color::colorizeTextBox($style['color'], ' ' . $style['symbol'] . ' ')
            );
        } else {
            $this->printer->print(' ' . $style['symbol'] . ' ');
        }

        $this->printer->print($test->prettifiedMethodName() . PHP_EOL);
    }

    /**
     * @psalm-return array{symbol: string, color: string, message: ?string}
     */
    private function style(TestStatus $status): array
    {
        if ($status->isSuccess()) {
            return [
                'symbol'  => '✔',
                'color'   => 'fg-green',
                'message' => null,
            ];
        }

        if ($status->isError()) {
            return [
                'symbol'  => '✘',
                'color'   => 'fg-yellow',
                'message' => 'bg-yellow,fg-black',
            ];
        }

        if ($status->isFailure()) {
            return [
                'symbol'  => '✘',
                'color'   => 'fg-red',
                'message' => 'bg-red,fg-white',
            ];
        }

        if ($status->isSkipped()) {
            return [
                'symbol'  => '↩',
                'color'   => 'fg-cyan',
                'message' => 'fg-cyan',
            ];
        }

        if ($status->isRisky()) {
            return [
                'symbol'  => '☢',
                'color'   => 'fg-yellow',
                'message' => 'fg-yellow',
            ];
        }

        if ($status->isIncomplete()) {
            return [
                'symbol'  => '∅',
                'color'   => 'fg-yellow',
                'message' => 'fg-yellow',
            ];
        }

        if ($status->isWarning()) {
            return [
                'symbol'  => '⚠',
                'color'   => 'fg-yellow',
                'message' => 'fg-yellow',
            ];
        }

        return [
            'symbol'  => '?',
            'color'   => 'fg-blue',
            'message' => 'fg-white,bg-blue',
        ];
    }
}
