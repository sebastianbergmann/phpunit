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
final readonly class TimeLimitExceeded implements Event
{
    private Telemetry\Info $telemetryInfo;

    /**
     * @var positive-int
     */
    private int $timeLimit;

    /**
     * @param positive-int $timeLimit
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, int $timeLimit)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->timeLimit     = $timeLimit;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    /**
     * The time limit for the test run in seconds.
     *
     * @return positive-int
     */
    public function timeLimit(): int
    {
        return $this->timeLimit;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $unit = 'seconds';

        if ($this->timeLimit === 1) {
            $unit = 'second';
        }

        return sprintf(
            'Test Runner Time Limit Exceeded (%d %s)',
            $this->timeLimit,
            $unit,
        );
    }
}
