<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Array.php';
require_once 'PHPUnit/Util/DualIterator.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Constraint which checks if one value is equal to another.
 *
 * Equality is checked with PHP's == operator, the operator is explained in detail
 * at {@url http://www.php.net/manual/en/types.comparisons.php}.
 * Two values are equal if they have the same value disregarding type.
 *
 * The expected value is passed in the constructor.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_Constraint_IsEqual implements PHPUnit_Framework_Constraint
{
    private $value;
    private $delta = 0;

    public function __construct($value, $delta = 0)
    {
        $this->value = $value;
        $this->delta = $delta;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @parameter mixed $other Value or object to evaluate.
     * @return bool
     */
    public function evaluate($other)
    {
        if  ((is_array($this->value)  && is_array($other)) ||
             (is_object($this->value) && is_object($other))) {
            if (is_object($this->value) && is_object($other)) {
                $value = (array) $this->value;
                $other = (array) $other;
            } else {
                $value = $this->value;
            }

            if (count($value) != count($other)) {
                return FALSE;
            }

            return PHPUnit_Util_DualIterator::compareIterators(
              new RecursiveIteratorIterator(
                new RecursiveArrayIterator(
                  PHPUnit_Util_Array::sortRecursively($value)
                ),
                RecursiveIteratorIterator::SELF_FIRST
              ),
              new RecursiveIteratorIterator(
                new RecursiveArrayIterator(
                  PHPUnit_Util_Array::sortRecursively($other)
                ),
                RecursiveIteratorIterator::SELF_FIRST
              ),
              FALSE,
              $this->delta
            );
        }

        else if (is_float($this->value) && is_float($other) && is_float($this->delta)) {
            return (abs($this->value - $other) <= $this->delta);
        }

        else {
            return $this->value == $other;
        }
    }

    /**
     * @param   mixed   $other The value passed to evaluate() which failed the
     *                         constraint check.
     * @param   string  $description A string with extra description of what was
     *                               going on while the evaluation failed.
     * @throws  PHPUnit_Framework_ExpectationFailedException
     */
    public function fail($other, $description)
    {
        throw new PHPUnit_Framework_ExpectationFailedException(
            $description,
            PHPUnit_Framework_ComparisonFailure::diffIdentical($this->value, $other)
        );
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     * @access public
     */
    public function toString()
    {
        $delta = '';
        $type  = '';
        $value = $this->value;

        if (is_string($value)) {
            if (strpos($value, "\n") !== FALSE) {
                return 'is equal to <text>';
            } else {
                return sprintf(
                  'is equal to <string:%s>',

                  $value
                );
            }
        } else {
            if ($this->delta != 0) {
                $delta = sprintf(
                  ' with delta <%d>',

                  $this->delta
                );
            }

            if (!is_null($value)) {
                $type = gettype($value) . ':';
            }

            return sprintf(
              'is equal to <%s>%s',

              $type  . print_r($value, TRUE),
              $delta
            );
        }
    }
}
?>
