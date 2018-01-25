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

use ReflectionObject;

/**
 * Constraint that asserts that the object it is evaluated for has a given
 * attribute.
 *
 * The attribute name is passed in the constructor.
 */
class ObjectHasAttribute extends ClassHasAttribute
{
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
        $object = new ReflectionObject($other);

        return $object->hasProperty($this->attributeName());
    }
}
