<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

/**
 * Logical XOR.
 */
final class LogicalXor extends Connective
{
    /**
     * Returns the name of this operator.
     */
    public function operator(): string
    {
        return 'xor';
    }

    /**
     * Returns this operator's precedence, as defined in
     * https://www.php.net/manual/en/language.operators.precedence.php
     */
    public function precedence(): int
    {
        return 23;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    public function matches($other): bool
    {
        $constraints = $this->constraints();

        if (($initial = \array_shift($constraints)) !== null) {
            return \array_reduce(
                $constraints,
                function ($carry, Constraint $constraint) use ($other) {
                    return $carry xor $constraint->evaluate($other, '', true);
                },
                $initial->evaluate($other, '', true)
            );
        } else {
            // $constraints was empty or not an array...
            return false;
        }
    }
}
