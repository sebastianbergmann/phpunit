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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CannotOpenSocketException;
use PHPUnit\TextUI\InvalidSocketException;
use PHPUnit\TextUI\Output\DefaultPrinter;

#[CoversClass(DefaultPrinter::class)]
#[Medium]
final class DefaultPrinterTest extends TestCase
{
    public static function providePrinter(): array
    {
        $data = [
            'standard output' => [DefaultPrinter::standardOutput()],
            'standard error'  => [DefaultPrinter::standardError()],
        ];

        try {
            $data['socket'] = [DefaultPrinter::from('socket://www.example.com:80')];
        } catch (CannotOpenSocketException $e) {
        }

        return $data;
    }

    #[DataProvider('providePrinter')]
    public function testFlush(DefaultPrinter $printer): void
    {
        $printer->flush();
        $this->expectOutputString('');
    }

    public function testInvalidSocket(): void
    {
        $this->expectException(InvalidSocketException::class);
        DefaultPrinter::from('socket://hostname:port:wrong');
    }
}
