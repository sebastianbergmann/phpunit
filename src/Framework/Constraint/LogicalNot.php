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

use PHPUnit\Framework\ExpectationFailedException;

/**
 * Logical NOT.
 */
class LogicalNot extends Constraint
{
    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @param Constraint $constraint
     */
    public function __construct($constraint)
    {
        parent::__construct();

        if (!($constraint instanceof Constraint)) {
            $constraint = new IsEqual($constraint);
        }

        $this->constraint = $constraint;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function negate($string): string
    {
        $positives = [
            'contains ',
            'exists',
            'has ',
            'is ',
            'are ',
            'matches ',
            'starts with ',
            'ends with ',
            'reference ',
            'not not '
        ];

        $negatives = [
            'does not contain ',
            'does not exist',
            'does not have ',
            'is not ',
            'are not ',
            'does not match ',
            'starts not with ',
            'ends not with ',
            'don\'t reference ',
            'not '
        ];

        \preg_match('/(\'[\w\W]*\')([\w\W]*)("[\w\W]*")/i', $string, $matches);

        if (\count($matches) > 0) {
            $nonInput = $matches[2];

            $negatedString = \str_replace(
                $nonInput,
                \str_replace(
                    $positives,
                    $negatives,
                    $nonInput
                ),
                $string
            );
        } else {
            $negatedString = \str_replace(
                $positives,
                $negatives,
                $string
            );
        }

        return $negatedString;
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
     * @param mixed  $other        value or object to evaluate
     * @param string $description  Additional information about the test
     * @param bool   $returnResult Whether to return a result or throw an exception
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \Exception
     *
     * @return mixed
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = !$this->constraint->evaluate($other, $description, true);

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
    public function toString(): string
    {
        switch (\get_class($this->constraint)) {
            case LogicalAnd::class:
            case self::class:
            case LogicalOr::class:
                return 'not( ' . $this->constraint->toString() . ' )';

            default:
                return self::negate(
                    $this->constraint->toString()
                );
        }
    }

    /**
     * Counts the number of constraint elements.
     *
     * @return int
     */
    public function count(): int
    {
        return \count($this->constraint);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return string
     */
    protected function failureDescription($other): string
    {
        switch (\get_class($this->constraint)) {
            case LogicalAnd::class:
            case self::class:
            case LogicalOr::class:
                return 'not( ' . $this->constraint->failureDescription($other) . ' )';

            default:
                return self::negate(
                    $this->constraint->failureDescription($other)
                );
        }
    }
}
