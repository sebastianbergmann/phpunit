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

use const STR_PAD_LEFT;
use function str_pad;
use InvalidArgumentException;

final class Duration
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

    public function asString(): string
    {
        // @TODO: Nice formatting
        return sprintf(
            '%d.%s',
            $this->seconds(),
            str_pad(
                (string) $this->nanoSeconds(),
                9,
                '0',
                STR_PAD_LEFT
            )
        );
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
