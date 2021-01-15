<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Tracer;

use Event\Tracer\LogDurationFormatter;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry\Info;

final class TextFileLogger implements Tracer
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function trace(Event $event): void
    {
        file_put_contents(
            $this->filename,
            $this->renderEvent($event),
            FILE_APPEND
        );
    }

    private function renderEvent(Event $event): string
    {
        return sprintf(
            "%s - %s\n",
            $this->renderTelemetry($event->telemetryInfo()),
            get_class($event)
        );
    }

    private function renderTelemetry(Info $telemetryInfo): string
    {
        $durationFormatter = new LogDurationFormatter();

        return sprintf(
            '[%s / %s] [%d Bytes]',
            $telemetryInfo->durationSinceStart()->asString($durationFormatter),
            $telemetryInfo->durationSincePrevious()->asString($durationFormatter),
            $telemetryInfo->memoryUsage()->bytes()
        );
    }
}
