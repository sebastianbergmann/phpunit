<?php
use PHPUnit\Framework\Constraint\Constraint;

final class TruthyConstraint extends Constraint
{
    public function matches($other): bool
    {
        return true;
    }

    public function toString(): string
    {
        return sprintf(
            'is accepted by %s',
            self::class
        );
    }
}
