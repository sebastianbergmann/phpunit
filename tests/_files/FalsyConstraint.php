<?php
use PHPUnit\Framework\Constraint\Constraint;

final class FalsyConstraint extends Constraint
{
    public function matches($other): bool
    {
        return false;
    }

    public function toString(): string
    {
        return sprintf(
            'is accepted by %s',
            self::class
        );
    }
}
