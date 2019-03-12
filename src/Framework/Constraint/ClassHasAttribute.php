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

use ReflectionClass;

/**
 * Constraint that asserts that the class it is evaluated for has a given
 * attribute.
 *
 * The attribute name is passed in the constructor.
 */
class ClassHasAttribute extends Constraint
{
    /**
     * @var string
     */
    private $attributeName;

    public function __construct(string $attributeName)
    {
        $this->attributeName = $attributeName;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return \sprintf(
            'has attribute "%s"',
            $this->attributeName
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     *
     * @throws \ReflectionException
     */
    protected function matches($other): bool
    {
        return (new ReflectionClass($other))->hasProperty($this->attributeName);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription($other): string
    {
        return \sprintf(
            '%sclass "%s" %s',
            \is_object($other) ? 'object of ' : '',
            \is_object($other) ? \get_class($other) : $other,
            $this->toString()
        );
    }

    protected function attributeName(): string
    {
        return $this->attributeName;
    }
}
