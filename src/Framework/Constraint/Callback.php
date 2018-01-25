<?php
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
 * Constraint that evaluates against a specified closure.
 */
class Callback extends Constraint
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        parent::__construct();

        $this->callback = $callback;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is accepted by specified callback';
    }

    /**
     * Evaluates the constraint for parameter $value. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        return \call_user_func($this->callback, $other);
    }
}
