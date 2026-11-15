<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ChildProcessFinished implements Event
{
    private Telemetry\Info $telemetryInfo;
    private ChildProcessReason $reason;
    private string $stdout;
    private string $stderr;
    private ?int $exitCode;
    private ?int $signal;

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, ChildProcessReason $reason, string $stdout, string $stderr, ?int $exitCode, ?int $signal)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->reason        = $reason;
        $this->stdout        = $stdout;
        $this->stderr        = $stderr;
        $this->exitCode      = $exitCode;
        $this->signal        = $signal;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function reason(): ChildProcessReason
    {
        return $this->reason;
    }

    public function stdout(): string
    {
        return $this->stdout;
    }

    public function stderr(): string
    {
        return $this->stderr;
    }

    /**
     * The exit code of the child process, or null when the child process was
     * terminated by a signal or when it is not known how it ended.
     */
    public function exitCode(): ?int
    {
        return $this->exitCode;
    }

    /**
     * The signal that terminated the child process, or null when the child
     * process was not terminated by a signal.
     */
    public function signal(): ?int
    {
        return $this->signal;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return sprintf(
            'Child Process Finished (%s%s)',
            $this->reason->value,
            $this->exitStatusAsString(),
        );
    }

    /**
     * The exit status is only named when the child process did not end
     * normally with exit code 0.
     */
    private function exitStatusAsString(): string
    {
        if ($this->signal !== null) {
            return sprintf(
                ', terminated by signal %d',
                $this->signal,
            );
        }

        if ($this->exitCode !== null && $this->exitCode !== 0) {
            return sprintf(
                ', exit code %d',
                $this->exitCode,
            );
        }

        return '';
    }
}
