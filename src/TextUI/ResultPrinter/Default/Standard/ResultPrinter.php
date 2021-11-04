<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\ResultPrinter\Standard;

use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\ResultPrinter\ResultPrinter as ResultPrinterInterface;
use PHPUnit\Util\Printer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ResultPrinter extends Printer implements ResultPrinterInterface
{
    public function printResult(TestResult $result): void
    {
    }

    public function write(string $buffer): void
    {
    }
}
