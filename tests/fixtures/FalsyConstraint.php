<?php
use PHPUnit\Framework\Constraint\Constraint;

final class FalsyConstraint extends Constraint
{
    public function matches($other)
    {
        return false;
    }

    public function toString()
    {
        return sprintf(
            'is accepted by %s',
            self::class
        );
    }
}
