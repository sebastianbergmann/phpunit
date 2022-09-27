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

use PHPUnit\Logging\TestDox\TestMethodCollection;
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
    }

    public function flush(): void
    {
        $this->printer->flush();
    }

    private function style(TestStatus $status): array
    {
        if ($status->isSuccess()) {
            return [
                'symbol' => '✔',
                'color'  => 'fg-green',
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
