<?php

class PHPUnit_Framework_Constraint_ClassHasConstant extends PHPUnit_Framework_Constraint
{
    /**
     * @var string
     */
    protected $constantName;

    /**
     * @param string $attributeName
     */
    public function __construct($constantName)
    {
        $this->constantName = $constantName;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    protected function matches($other)
    {
        $class = new ReflectionClass($other);

        return $class->hasConstant($this->constantName);
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
          'has constant "%s"',

          $this->constantName
        );
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param  mixed $other Evaluated value or object.
     * @return string
     */
    protected function failureDescription($other)
    {
        return sprintf(
          '%sclass "%s" %s',

          is_object($other) ? 'object of ' : '',
          is_object($other) ? get_class($other) : $other,
          $this->toString()
        );
    }
}
