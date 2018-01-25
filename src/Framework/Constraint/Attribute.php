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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

class Attribute extends Composite
{
    /**
     * @var string
     */
    private $attributeName;

    /**
     * @param Constraint $constraint
     * @param string     $attributeName
     */
    public function __construct(Constraint $constraint, $attributeName)
    {
        parent::__construct($constraint);

        $this->attributeName = $attributeName;
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
     * @throws \Exception
     * @throws \PHPUnit\Framework\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return mixed
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        return parent::evaluate(
            Assert::readAttribute(
                $other,
                $this->attributeName
            ),
            $description,
            $returnResult
        );
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString(): string
    {
        return 'attribute "' . $this->attributeName . '" ' . $this->innerConstraint()->toString();
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @return string
     */
    protected function failureDescription($other): string
    {
        return $this->toString();
    }
}
