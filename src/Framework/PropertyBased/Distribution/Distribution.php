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
 * Determines how a choice is drawn at random.
 *
 * A distribution only affects which choices are drawn while examples are
 * generated. It is not consulted when a choice sequence is replayed, so it
 * does not affect shrinking.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Distribution
{
    /**
     * @param non-negative-int $maximum
     *
     * @return non-negative-int a choice that is not greater than $maximum
     */
    public function sample(Randomizer $randomizer, int $maximum): int;
}
