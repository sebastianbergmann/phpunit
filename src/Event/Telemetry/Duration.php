<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetry;

use function floor;
use InvalidArgumentException;

final class Duration
{
    private int $seconds;

    private int $nanoseconds;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $seconds, int $nanoseconds)
    {
        $this->ensureNotNegativeInt($seconds, 'second');
        $this->ensureNotNegativeInt($nanoseconds, 'nanosecond');

        $this->seconds     = $seconds;
        $this->nanoseconds = $nanoseconds;
    }

    public function seconds(): int
    {
        return $this->seconds;
    }

    public function nanoseconds(): int
    {
        return $this->nanoseconds;
    }

    public function asString(DurationFormatter $formatter = null): string
    {
        if ($formatter !== null) {
            return $formatter->format($this);
        }

        $formatted = '';
        $seconds   = $this->seconds();

        if ($seconds > 60 * 60) {
            $hours     = floor($seconds / 60 / 60);
            $formatted = \sprintf('%02d', $hours) . ':';
            $seconds -= ($hours * 60 * 60);
        }

        if ($seconds > 60) {
            $minutes = floor($seconds / 60);
            $formatted .= \sprintf('%02d', $minutes) . ':';
            $seconds -= ($minutes * 60);
        }

        $formatted .= \sprintf('%02d', $seconds) . '.';
        $formatted .= \sprintf('%09d', $this->nanoseconds());

        return $formatted;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureNotNegativeInt(int $value, string $which): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(sprintf(
                'Value for %s must not be negative.',
                $which
            ));
        }
    }
}
