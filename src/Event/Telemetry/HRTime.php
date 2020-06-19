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

use InvalidArgumentException;

final class HRTime
{
    private int $seconds;

    private int $nanoSeconds;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $seconds, int $nanoSeconds)
    {
        $this->ensureNotNegativeInt($seconds, 'second');
        $this->ensureNotNegativeInt($nanoSeconds, 'nanosecond');

        $this->seconds     = $seconds;
        $this->nanoSeconds = $nanoSeconds;
    }

    public function seconds(): int
    {
        return $this->seconds;
    }

    public function nanoSeconds(): int
    {
        return $this->nanoSeconds;
    }

    public function duration(self $other): Duration
    {
        $seconds     = $this->seconds;
        $nanoSeconds = $this->nanoSeconds;

        $nanoDuration = $nanoSeconds - $other->nanoSeconds();

        if ($nanoDuration < 0) {
            $seconds--;
            $nanoSeconds += 1000000000;

            $nanoDuration = $nanoSeconds - $other->nanoSeconds();
        }

        $secondDuration = $seconds - $other->seconds();

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
