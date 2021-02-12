<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry\Info;

final class EventWithFixedStringRepresentation implements Event
{
    private string $stringRepresentation;

    public function __construct(string $stringRepresentation)
    {
        $this->stringRepresentation = $stringRepresentation;
    }

    public function telemetryInfo(): Info
    {
    }

    public function asString(): string
    {
        return $this->stringRepresentation;
    }
}
