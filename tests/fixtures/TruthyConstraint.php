<?php
use PHPUnit\Framework\Constraint\Constraint;

final class TruthyConstraint extends Constraint
{
    public function matches($other)
    {
        return true;
    }

    public function toString()
    {
        return sprintf(
            'is accepted by %s',
            self::class
        );
    }
}
