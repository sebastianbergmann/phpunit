<?php

namespace PHPUnit\Event\TestRunner;

use PHPUnit\Event\Telemetry\Event;

class PhpunitDeprecationTriggered extends Event
{
    public function __construct(string $message, string $file, int $line)
    {
        parent::__construct($message, $file, $line);
    }
}
