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
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Márcio Almada <marcio3w@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 4.4.0
 */
class PHPUnit_Framework_Constraint_ArraySubset extends PHPUnit_Framework_Constraint
{
    /**
     * @var array|ArrayAccess
     */
    protected $subset;

    /**
     * @var boolean
     */
    protected $strict;

    /**
     * @param array|ArrayAccess $subset
     * @param boolean           $strict Check for object identity
     */
    public function __construct($subset, $strict = false)
    {
        parent::__construct();
        $this->strict  = $strict;
        $this->subset = $subset;
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
     * @param  mixed                                        $other        Value or object to evaluate.
     * @param  string                                       $description  Additional information about the test
     * @param  bool                                         $returnResult Whether to return a result or throw an exception
     * @return mixed
     * @throws PHPUnit_Framework_ExpectationFailedException
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = false;

        $patched = array_replace_recursive($other, $this->subset);

        if ($this->strict) {
            $success = $other === $patched;
        } else {
            $success = $other == $patched;
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $f = new SebastianBergmann\Comparator\ComparisonFailure(
                $patched,
                $other,
                print_r($patched, true),
                print_r($other, true)
            );
            $this->fail($other, $description, $f);
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
     * @param  mixed  $other Evaluated value or object.
     * @return string
     */
    protected function failureDescription($other)
    {
        return 'an array ' . $this->toString();
    }
}
