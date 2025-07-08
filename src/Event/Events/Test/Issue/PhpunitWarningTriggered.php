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
use function implode;
use function sprintf;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PhpunitWarningTriggered implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Test $test;

    /**
     * @var non-empty-string
     */
    private string $message;
    private bool $ignoredByTest;

    /**
     * @param non-empty-string $message
     */
    public function __construct(Telemetry\Info $telemetryInfo, Test $test, string $message, bool $ignoredByTest)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->test          = $test;
        $this->message       = $message;
        $this->ignoredByTest = $ignoredByTest;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function test(): Test
    {
        return $this->test;
    }

    /**
     * @return non-empty-string
     */
    public function message(): string
    {
        return $this->message;
    }

    public function ignoredByTest(): bool
    {
        return $this->ignoredByTest;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $message = $this->message;

        if ($message !== '') {
            $message = PHP_EOL . $message;
        }

        $details = [$this->test->id()];

        if ($this->ignoredByTest) {
            $details[] = 'ignored by test';
        }

        return sprintf(
            'Test Triggered PHPUnit Warning (%s)%s',
            implode(', ', $details),
            $message,
        );
    }
}
