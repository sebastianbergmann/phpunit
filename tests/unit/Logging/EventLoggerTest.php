<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging;

use const PHP_EOL;
use function file_get_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogger::class)]
#[Small]
final class EventLoggerTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), __CLASS__);
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testLogsEventWithoutTelemetryInfo(): void
    {
        $logger = new EventLogger($this->file, false);

        $logger->trace($this->event('the event'));

        $this->assertSame('the event' . PHP_EOL, file_get_contents($this->file));
    }

    public function testLogsEventWithTelemetryInfo(): void
    {
        $logger = new EventLogger($this->file, true);

        $logger->trace($this->event('the event'));

        $this->assertSame(
            '[00:02:03.000000456 / 00:03:54.000000567] [2000 bytes] the event' . PHP_EOL,
            file_get_contents($this->file),
        );
    }

    #[TestDox('Indents continuation lines of a multi-line event')]
    public function testIndentsContinuationLinesOfMultiLineEvent(): void
    {
        $logger = new EventLogger($this->file, false);

        $logger->trace($this->event("first line\nsecond line"));

        $this->assertSame(
            'first line' . PHP_EOL . 'second line' . PHP_EOL,
            file_get_contents($this->file),
        );
    }

    #[TestDox('Indents continuation lines of a multi-line event by the width of the telemetry info')]
    public function testIndentsContinuationLinesOfMultiLineEventByWidthOfTelemetryInfo(): void
    {
        $logger = new EventLogger($this->file, true);

        $logger->trace($this->event("first line\r\nsecond line\rthird line"));

        $this->assertSame(
            '[00:02:03.000000456 / 00:03:54.000000567] [2000 bytes] first line' . PHP_EOL .
            '                                                       second line' . PHP_EOL .
            '                                                       third line' . PHP_EOL,
            file_get_contents($this->file),
        );
    }

    public function testAppendsToPreviouslyLoggedEvents(): void
    {
        $logger = new EventLogger($this->file, false);

        $logger->trace($this->event('the first event'));
        $logger->trace($this->event('the second event'));

        $this->assertSame(
            'the first event' . PHP_EOL . 'the second event' . PHP_EOL,
            file_get_contents($this->file),
        );
    }

    /**
     * @param non-empty-string $string
     */
    private function event(string $string): Event
    {
        $event = $this->createStub(Event::class);

        $event->method('asString')->willReturn($string);
        $event->method('telemetryInfo')->willReturn($this->telemetryInfo())->seal();

        return $event;
    }

    private function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(0, 0),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
        );
    }
}
