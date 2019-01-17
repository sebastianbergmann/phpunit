<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Constraint\Constraint;

final class TruthyConstraint extends Constraint
{
    public function matches($other): bool
    {
        return true;
    }

    public function toString(): string
    {
        return \sprintf(
            'is accepted by %s',
            self::class
        );
    }
}
