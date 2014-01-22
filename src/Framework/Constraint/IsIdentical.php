<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Constraint that asserts that one value is identical to another.
 *
 * Identical check is performed with PHP's === operator, the operator is
 * explained in detail at
 * {@url http://www.php.net/manual/en/types.comparisons.php}.
 * Two values are identical if they have the same value and are of the same
 * type.
 *
 * The expected value is passed in the constructor.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_Constraint_IsIdentical extends PHPUnit_Framework_Constraint
{
    /**
     * @var float
     */
    const EPSILON = 0.0000000001;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
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
        if (is_double($this->value) && is_double($other) &&
            !is_infinite($this->value) && !is_infinite($other) &&
            !is_nan($this->value) && !is_nan($other)) {
            $success = abs($this->value - $other) < self::EPSILON;
        } else {
            $success = $this->value === $other;
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $f = null;

            // if both values are strings, make sure a diff is generated
            if (is_string($this->value) && is_string($other)) {
                $f = new SebastianBergmann\Comparator\ComparisonFailure(
                  $this->value,
                  $other,
                  $this->value,
                  $other
                );
            }

            $this->fail($other, $description, $f);
        }
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
        if (is_object($this->value) && is_object($other)) {
            return 'two variables reference the same object';
        }

        if (is_string($this->value) && is_string($other)) {
            return 'two strings are identical';
        }

        return parent::failureDescription($other);
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        if (is_object($this->value)) {
            return 'is identical to an object of class "' .
                   get_class($this->value) . '"';
        } else {
            return 'is identical to ' .
                   $this->exporter->export($this->value);
        }
    }
}
