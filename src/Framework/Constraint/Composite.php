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

use function count;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 *
 * @codeCoverageIgnore
 */
abstract class Composite extends Constraint
{
    /**
     * @var Constraint
     */
    private $innerConstraint;

    public function __construct(Constraint $innerConstraint)
    {
        $this->innerConstraint = $innerConstraint;
    }

    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function evaluate($other, string $description = '', bool $returnResult = false)
    {
        try {
            return $this->innerConstraint->evaluate(
                $other,
                $description,
                $returnResult
            );
        } catch (ExpectationFailedException $e) {
            $this->fail($other, $description, $e->getComparisonFailure());
        }
    }

    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        return count($this->innerConstraint);
    }

    protected function innerConstraint(): Constraint
    {
        return $this->innerConstraint;
    }
}
