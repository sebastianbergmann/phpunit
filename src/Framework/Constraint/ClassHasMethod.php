<?php

namespace PHPUnit\Framework\Constraint;

use ReflectionClass;

class ClassHasMethod extends Constraint
{
    /**
     * @var string
     */
    protected $methodName;

    public function __construct(string $methodName)
    {
        parent::__construct();

        $this->methodName = $methodName;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString(): string
    {
        return \sprintf(
            'has method "%s"',
            $this->methodName
        );
    }

    /**
     * Evaluates the constraint for method $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        $class = new ReflectionClass($other);

        return $class->hasMethod($this->methodName);
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
        return \sprintf(
            '%sclass "%s" %s',
            \is_object($other) ? 'object of ' : 'class of ',
            \is_object($other) ? \get_class($other) : $other,
            $this->toString()
        );
    }
}
