<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function assert;

/**
 * The part of a choice sequence that a generator, or one element of a
 * collection, consumed.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Span
{
    /**
     * @var non-negative-int
     */
    private int $start;

    /**
     * @var positive-int
     */
    private int $end;

    /**
     * @param non-negative-int $start the index of the first choice of the span
     * @param positive-int     $end   the index after the last choice of the span
     */
    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    /**
     * @return non-negative-int
     */
    public function start(): int
    {
        return $this->start;
    }

    /**
     * @return positive-int
     */
    public function end(): int
    {
        return $this->end;
    }

    /**
     * @return positive-int
     */
    public function length(): int
    {
        $length = $this->end - $this->start;

        assert($length > 0);

        return $length;
    }
}
