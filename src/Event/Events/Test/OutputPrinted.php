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

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class OutputPrinted implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Code\Test $test;
    private string $output;

    public function __construct(Telemetry\Info $telemetryInfo, Code\Test $test, string $output)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->test          = $test;
        $this->output        = $output;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function test(): Code\Test
    {
        return $this->test;
    }

    public function output(): string
    {
        return $this->output;
    }

    public function asString(): string
    {
        return sprintf(
            'Test Printed Output%s%s',
            PHP_EOL,
            $this->output
        );
    }
}
