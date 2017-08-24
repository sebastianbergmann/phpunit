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

use PHPUnit\Framework\ExpectationFailedException;

/**
 * Logical AND.
 */
class LogicalAnd extends Constraint
{
    /**
     * @var Constraint[]
     */
    protected $constraints = [];

    /**
     * @var Constraint
     */
    protected $lastConstraint;

    /**
     * @param Constraint[] $constraints
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function setConstraints(array $constraints)
    {
        $this->constraints = [];

        foreach ($constraints as $constraint) {
            if (!($constraint instanceof Constraint)) {
                throw new \PHPUnit\Framework\Exception(
                    'All parameters to ' . __CLASS__ .
                    ' must be a constraint object.'
                );
            }

            $this->constraints[] = $constraint;
        }
    }

    /**
     * Evaluates the constraint for parameter $other
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @param mixed  $other        Value or object to evaluate.
     * @param string $description  Additional information about the test
     * @param bool   $returnResult Whether to return a result or throw an exception
     *
     * @return mixed
     *
     * @throws ExpectationFailedException
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success    = true;
        $constraint = null;

        foreach ($this->constraints as $constraint) {
            if (!$constraint->evaluate($other, $description, true)) {
                $success = false;
                break;
            }
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail($other, $description);
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        $text = '';

        foreach ($this->constraints as $key => $constraint) {
            if ($key > 0) {
                $text .= ' and ';
            }

            $text .= $constraint->toString();
        }

        return $text;
    }

    /**
     * Counts the number of constraint elements.
     *
     * @return int
     */
    public function count()
    {
        $count = 0;

        foreach ($this->constraints as $constraint) {
            $count += \count($constraint);
        }

        return $count;
    }
}
