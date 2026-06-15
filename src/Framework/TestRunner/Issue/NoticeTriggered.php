<?php

namespace PHPUnit\Event\TestRunner\Issue;

use PHPUnit\Event\Telemetry\Event;

class NoticeTriggered extends Event
{
    public function __construct(string $message, string $file, int $line)
    {
        parent::__construct($message, $file, $line);
    }
}
