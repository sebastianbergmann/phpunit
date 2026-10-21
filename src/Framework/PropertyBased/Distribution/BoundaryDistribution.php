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

use const PHP_INT_MAX;
use function assert;
use function min;
use Random\Randomizer;

/**
 * Draws the boundaries of the range, 0 and the maximum, and small choices
 * with a significant probability. Otherwise, the choice is drawn
 * logarithmically, so that choices of every magnitude occur.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BoundaryDistribution implements Distribution
{
    private const int LARGEST_SMALL_CHOICE = 16;

    public function sample(Randomizer $randomizer, int $maximum): int
    {
        $probability = $randomizer->nextFloat();

        if ($probability < 0.1) {
            return 0;
        }

        if ($probability < 0.2) {
            return $maximum;
        }

        if ($probability < 0.5) {
            return $randomizer->getInt(0, min($maximum, self::LARGEST_SMALL_CHOICE));
        }

        return $this->logarithmic($randomizer, $maximum);
    }

    /**
     * Draws the number of bits of the choice uniformly, and then the choice
     * uniformly among the choices with that number of bits.
     *
     * @param non-negative-int $maximum
     *
     * @return non-negative-int
     */
    private function logarithmic(Randomizer $randomizer, int $maximum): int
    {
        $numberOfBits = $randomizer->getInt(0, $this->numberOfBits($maximum));

        if ($numberOfBits === 0) {
            return 0;
        }

        $lower = 1 << ($numberOfBits - 1);

        if ($numberOfBits >= 63) {
            $upper = PHP_INT_MAX;
        } else {
            $upper = (1 << $numberOfBits) - 1;
        }

        $choice = $randomizer->getInt($lower, min($upper, $maximum));

        assert($choice >= 0);

        return $choice;
    }

    /**
     * @param non-negative-int $value
     *
     * @return non-negative-int
     */
    private function numberOfBits(int $value): int
    {
        $numberOfBits = 0;

        while ($value > 0) {
            $value >>= 1;

            $numberOfBits++;
        }

        return $numberOfBits;
    }
}
