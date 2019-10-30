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
final class LogicalAnd extends Constraint
{
    /**
     * @var Constraint[]
     */
    private $constraints = [];

    public static function fromConstraints(Constraint ...$constraints): self
    {
        $constraint = new self;

        $constraint->constraints = \array_values($constraints);

        return $constraint;
    }

    /**
     * @param Constraint[] $constraints
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function setConstraints(array $constraints): void
    {
        $this->constraints = [];

        foreach ($constraints as $constraint) {
            if (!($constraint instanceof Constraint)) {
                throw new \PHPUnit\Framework\Exception(
                    'All parameters to ' . __CLASS__ . ' must be a constraint object.'
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
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return null|bool
     */
    public function evaluate($other, string $description = '', bool $returnResult = false)
    {
        $success = true;

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

        return null;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
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
     */
    public function count(): int
    {
        $count = 0;

        foreach ($this->constraints as $constraint) {
            $count += \count($constraint);
        }

        return $count;
    }
}
