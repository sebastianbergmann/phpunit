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
 * Constraint that asserts that the value it is evaluated for is less than
 * a given value.
 */
class LessThan extends Constraint
{
    /**
     * @var int|float
     */
    protected $value;

    /**
     * @param int|float $value
     */
    public function __construct($value)
    {
        parent::__construct();

        $this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     *
     * @return bool
     */
    protected function matches($other)
    {
        return $this->value > $other;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'is less than ' . $this->exporter->export($this->value);
    }
}
