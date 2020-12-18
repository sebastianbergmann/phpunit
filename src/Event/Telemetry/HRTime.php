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

use function sprintf;
use InvalidArgumentException;

final class HRTime
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

    /**
     * @throws InvalidArgumentException
     */
    public function duration(self $other): Duration
    {
        $seconds     = $this->seconds;
        $nanoseconds = $this->nanoseconds;

        $nanoDuration = $nanoseconds - $other->nanoseconds();

        if ($nanoDuration < 0) {
            $seconds--;
            $nanoseconds += 1000000000;

            $nanoDuration = $nanoseconds - $other->nanoseconds();
        }

        $secondDuration = $seconds - $other->seconds();

        if ($secondDuration < 0) {
            throw new InvalidArgumentException('Other needs to be greater.');
        }

        return new Duration($secondDuration, $nanoDuration);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureNotNegativeInt(int $value, string $which): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(sprintf(
                'Value for %s must not be negative',
                $which
            ));
        }
    }
}
