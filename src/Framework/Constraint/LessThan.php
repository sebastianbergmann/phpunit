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
 * Constraint that asserts that the value it is evaluated for is less than
 * a given value.
 */
class LessThan extends Constraint
{
    /**
     * @var float|int
     */
    private $value;

    /**
     * @param float|int $value
     */
    public function __construct($value)
    {
        parent::__construct();

        $this->value = $value;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return string
     */
    public function toString(): string
    {
        return 'is less than ' . $this->exporter->export($this->value);
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        return $this->value > $other;
    }
}
