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

use function fclose;
use function fread;
use function stream_socket_accept;
use function stream_socket_get_name;
use function stream_socket_server;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CannotOpenSocketException;
use PHPUnit\TextUI\InvalidSocketException;
use PHPUnit\TextUI\Output\DefaultPrinter;

#[CoversClass(DefaultPrinter::class)]
#[CoversClass(CannotOpenSocketException::class)]
#[CoversClass(InvalidSocketException::class)]
#[Medium]
final class DefaultPrinterTest extends TestCase
{
    /**
     * @return array<string, array{DefaultPrinter}>
     */
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

    public function testCanBeCreatedForStandardOutput(): void
    {
        $this->expectOutputString('');

        $printer = DefaultPrinter::standardOutput();

        $printer->flush();
    }

    public function testCanBeCreatedForStandardError(): void
    {
        $this->expectOutputString('');

        $printer = DefaultPrinter::standardError();

        $printer->flush();
    }

    public function testCanWriteToSocket(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);

        $this->assertIsResource($server);

        $address = stream_socket_get_name($server, false);

        $this->assertIsString($address);

        $printer = DefaultPrinter::from('socket://' . $address);

        $printer->print('message');
        $printer->flush();

        $connection = stream_socket_accept($server);

        $this->assertIsResource($connection);

        $this->assertSame('message', fread($connection, 7));

        fclose($connection);
        fclose($server);
    }

    public function testCannotBeCreatedWhenSocketCannotBeOpened(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);

        $this->assertIsResource($server);

        $address = stream_socket_get_name($server, false);

        $this->assertIsString($address);

        fclose($server);

        $this->expectException(CannotOpenSocketException::class);
        $this->expectExceptionMessage('Cannot open socket ' . $address);

        DefaultPrinter::from('socket://' . $address);
    }
}
