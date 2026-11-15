<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Application;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use PHPUnit\Runner\Receipt\Outcome;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Finished implements Event
{
    private Telemetry\Info $telemetryInfo;
    private Outcome $outcome;

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, Outcome $outcome)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->outcome       = $outcome;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function shellExitCode(): int
    {
        return $this->outcome->shellExitCode();
    }

    /**
     * How the test run ended, as the receipt of the test run records it:
     * whether every selected test was run, the shell exit code, the numbers
     * of tests by outcome, and the numbers of the issues that were triggered
     * by tests and by the test runner.
     */
    public function outcome(): Outcome
    {
        return $this->outcome;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return sprintf(
            'PHPUnit Finished (Shell Exit Code: %d)',
            $this->outcome->shellExitCode(),
        );
    }
}
