<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Printer;

/**
 * @internal
 *
 * @covers \PHPUnit\TextUI\TraceResultPrinter
 */
final class TraceResultPrinterTest extends TestCase
{
    public function testWriteUsesInternalPrinterToWrite(): void
    {
        $buffer = 'Hello, getting started with printing something here!';

        $internalPrinter = $this->createMock(Printer::class);

        $internalPrinter
            ->expects($this->once())
            ->method('write')
            ->with($this->identicalTo($buffer));

        $printer = new TraceResultPrinter($internalPrinter);

        $printer->write($buffer);
    }
}
