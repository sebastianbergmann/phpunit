<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\TextUI\DefaultResultPrinter;

class DefaultResultPrinterTest extends \PHPUnit\Framework\TestCase
{
    public function testInvalidColorOptionThrowsException(): void
    {
        $this->expectException(\PHPUnit\Framework\InvalidArgumentException::class);
        new DefaultResultPrinter(null, false, 'COLOR_GARBAGE');
    }

    public function testInvalidColumnsOptionThrowsException(): void
    {
        $this->expectException(\PHPUnit\Framework\InvalidArgumentException::class);
        new DefaultResultPrinter(null, false, DefaultResultPrinter::COLOR_DEFAULT, false, -1);
    }
}
