<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use SebastianBergmann\CodeUnit;

final class AbortedWithMessage implements Event
{
    private Telemetry\Info $telemetryInfo;

    private CodeUnit\ClassMethodUnit $testMethod;

    private string $message;

    public function __construct(Telemetry\Info $telemetryInfo, CodeUnit\ClassMethodUnit $testMethod, string $message)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->testMethod    = $testMethod;
        $this->message       = $message;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testMethod(): CodeUnit\ClassMethodUnit
    {
        return $this->testMethod;
    }

    public function message(): string
    {
        return $this->message;
    }
}
