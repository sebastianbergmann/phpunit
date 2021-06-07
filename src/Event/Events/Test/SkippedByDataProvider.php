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

use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class SkippedByDataProvider implements Event
{
    private Telemetry\Info $telemetryInfo;

    private Code\ClassMethod $testMethod;

    private string $message;

    public function __construct(Telemetry\Info $telemetryInfo, Code\ClassMethod $testMethod, string $message)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->testMethod    = $testMethod;
        $this->message       = $message;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testMethod(): Code\ClassMethod
    {
        return $this->testMethod;
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @todo
     */
    public function asString(): string
    {
        return sprintf(
            '%s %s todo',
            $this->telemetryInfo()->asString(),
            self::class
        );
    }
}
