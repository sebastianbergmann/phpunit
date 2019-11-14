<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use function date_default_timezone_get;
use DateTimeZone;
use PHPUnit\Event\Telemetry\System;
use PHPUnit\Event\Telemetry\SystemClock;
use PHPUnit\Event\Telemetry\SystemMemoryMeter;

final class Facade
{
    private ?TypeMap $typeMap = null;

    private ?Emitter $emitter = null;

    /**
     * @param class-string $subscriberInterface
     * @param class-string $eventClass
     */
    public function registerTypeMapping(string $subscriberInterface, string $eventClass): void
    {
        $this->typeMap()->addMapping($subscriberInterface, $eventClass);
    }

    public function emitter(): Emitter
    {
        if ($this->emitter === null) {
            $this->emitter = new Emitter(
                new Dispatcher($this->typeMap()),
                new System(
                    new SystemClock(new DateTimeZone(date_default_timezone_get())),
                    new SystemMemoryMeter()
                )
            );
        }

        return $this->emitter;
    }

    private function typeMap(): TypeMap
    {
        if ($this->typeMap === null) {
            $this->typeMap = new TypeMap();
        }

        return $this->typeMap;
    }
}
