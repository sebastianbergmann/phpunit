<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Distribution;

use function min;
use Random\Randomizer;

/**
 * Always draws the given choice, clamped to the maximum. A generator uses it
 * to produce a value that consists of several choices, such as a special
 * floating-point number, with a significant probability.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExactDistribution implements Distribution
{
    /**
     * @var non-negative-int
     */
    private int $choice;

    /**
     * @param non-negative-int $choice
     */
    public function __construct(int $choice)
    {
        $this->choice = $choice;
    }

    public function sample(Randomizer $randomizer, int $maximum): int
    {
        return min($this->choice, $maximum);
    }
}
