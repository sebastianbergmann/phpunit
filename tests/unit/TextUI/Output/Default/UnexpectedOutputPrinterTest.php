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

use function hrtime;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\PrintedUnexpectedOutput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Output\Printer;

#[CoversClass(UnexpectedOutputPrinter::class)]
#[Small]
final class UnexpectedOutputPrinterTest extends TestCase
{
    public function testForwardsOutputFromEventToPrinter(): void
    {
        $printer = $this->createMock(Printer::class);
        $printer->expects($this->once())->method('print')->with('unexpected output');

        $unexpectedOutputPrinter = new UnexpectedOutputPrinter($printer, new EventFacade);

        $unexpectedOutputPrinter->notify(
            new PrintedUnexpectedOutput($this->telemetryInfo(), 'unexpected output'),
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
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
        );
    }
}
