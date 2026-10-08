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

use Random\Randomizer;

/**
 * Draws the choice 1 with the given probability and the choice 0 otherwise.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BernoulliDistribution implements Distribution
{
    private float $probabilityOfOne;

    public function __construct(float $probabilityOfOne)
    {
        $this->probabilityOfOne = $probabilityOfOne;
    }

    public function sample(Randomizer $randomizer, int $maximum): int
    {
        if ($maximum === 0) {
            return 0;
        }

        if ($randomizer->nextFloat() < $this->probabilityOfOne) {
            return 1;
        }

        return 0;
    }
}
