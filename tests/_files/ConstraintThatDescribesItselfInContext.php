<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\Operator;

final class ConstraintThatDescribesItselfInContext extends Constraint
{
    public function toString(): string
    {
        return 'is described';
    }

    protected function toStringInContext(Operator $operator, mixed $role): string
    {
        return 'is described in the context of ' . $operator->operator();
    }
}
