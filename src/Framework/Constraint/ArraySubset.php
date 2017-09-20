<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Constraint that asserts that the array it is evaluated for has a specified subset.
 *
 * Uses array_replace_recursive() to check if a key value subset is part of the
 * subject array.
 *
 * @since Class available since Release 4.4.0
 */
class PHPUnit_Framework_Constraint_ArraySubset extends PHPUnit_Framework_Constraint
{
    /**
     * @var array|ArrayAccess
     */
    protected $subset;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * @param array|ArrayAccess $subset
     * @param bool              $strict Check for object identity
     */
    public function __construct($subset, $strict = false)
    {
        parent::__construct();
        $this->strict = $strict;
        $this->subset = $subset;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param array|ArrayAccess $other Array or ArrayAccess object to evaluate.
     *
     * @return bool
     */
    protected function matches($other)
    {
        //type cast $other & $this->subset as an array to allow
        //support in standard array functions.
        if ($other instanceof \ArrayAccess) {
            $other = (array) $other;
        }

        if ($this->subset instanceof \ArrayAccess) {
            $this->subset = (array) $this->subset;
        }

        if (!$this->isArrayAssociative($this->subset)) {
            $diff = \array_uintersect($other, $this->subset, function ($other_value, $subset_value) {
                if ($this->strict) {
                    if ($other_value === $subset_value) {
                        return 0;
                    }
                } else {
                    if ($other_value == $subset_value) {
                        return 0;
                    }
                }
                if ($other_value > $subset_value) {
                    return 1;
                }
                return -1;
            });

            return !empty($diff);
        } else {
            $patched = \array_replace_recursive($other, $this->subset);

            if ($this->strict) {
                return $other === $patched;
            } else {
                return $other == $patched;
            }
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'has the subset ' . $this->exporter->export($this->subset);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other Evaluated value or object.
     *
     * @return string
     */
    protected function failureDescription($other)
    {
        return 'an array ' . $this->toString();
    }

    /**
     * @param array $subject
     *
     * @return bool
     */
    private function isArrayAssociative(array $subject)
    {
        return !empty(\array_filter(array_keys($subject), 'is_string'));
    }
}
