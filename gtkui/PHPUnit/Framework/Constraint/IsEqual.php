<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @category   Testing
 * @package    PHPUnit
 * @author     Kore Nordmann <kn@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Array.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Type.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Constraint that checks if one value is equal to another.
 *
 * Equality is checked with PHP's == operator, the operator is explained in detail
 * at {@url http://www.php.net/manual/en/types.comparisons.php}.
 * Two values are equal if they have the same value disregarding type.
 *
 * The expected value is passed in the constructor.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Kore Nordmann <kn@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_Constraint_IsEqual extends PHPUnit_Framework_Constraint
{
    private $value;
    private $delta = 0;
    private $maxDepth = 10;

    public function __construct($value, $delta = 0, $maxDepth = 10)
    {
        $this->value    = $value;
        $this->delta    = $delta;
        $this->maxDepth = $maxDepth;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    public function evaluate($other)
    {
        return $this->recursiveComparison($this->value, $other);
    }

    /**
     * @param   mixed   $other The value passed to evaluate() which failed the
     *                         constraint check.
     * @param   string  $description A string with extra description of what was
     *                               going on while the evaluation failed.
     * @param   boolean $not Flag to indicate negation.
     * @throws  PHPUnit_Framework_ExpectationFailedException
     */
    public function fail($other, $description, $not = FALSE)
    {
        $failureDescription = $this->failureDescription(
          $other,
          $description,
          $not
        );

        if (!$not) {
            throw new PHPUnit_Framework_ExpectationFailedException(
              $failureDescription,
              PHPUnit_Framework_ComparisonFailure::diffEqual($this->value, $other)
            );
        } else {
            throw new PHPUnit_Framework_ExpectationFailedException(
              $failureDescription
            );
        }
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

        if (is_string($this->value)) {
            if (strpos($this->value, "\n") !== FALSE) {
                return 'is equal to <text>';
            } else {
                return sprintf(
                  'is equal to <string:%s>',

                  $this->value
                );
            }
        } else {
            if ($this->delta != 0) {
                $delta = sprintf(
                  ' with delta <%f>',

                  $this->delta
                );
            }

            return sprintf(
              'is equal to %s%s',

              PHPUnit_Util_Type::toString($this->value),
              $delta
            );
        }
    }

    /**
     * Perform the actual recursive comparision of two values
     * 
     * @param mixed $a First value
     * @param mixed $b Second value
     * @param int $depth Depth
     * @return bool
     */
    protected function recursiveComparison($a, $b, $depth = 0)
    {
        if ($depth >= $this->maxDepth) {
            return TRUE;
        }

        if (is_array($a) XOR is_array($b)) {
            return FALSE;
        }

        if (is_object($a) XOR is_object($b)) {
            return FALSE;
        }

        if (is_object($a) && is_object($b) &&
           (get_class($a) !== get_class($b))) {
            return FALSE;
        }

        // Normal comparision for scalar values.
        if ((!is_array($a) && !is_object($a)) ||
            (!is_array($b) && !is_object($b))) {
            if (is_numeric($a) && is_numeric($b)) {
                // Optionally apply delta on numeric values.
                return $this->numericComparison($a, $b);
            } else {
                return ($a == $b);
            }
        }

        if (is_object($a)) {
            $a = (array) $a;
            $b = (array) $b;
        }

        foreach ($a as $key => $v) {
            if (!array_key_exists($key, $b)) {
                // Abort on missing key in $b.
                return FALSE;
            }

            if (!$this->recursiveComparison($a[$key], $b[$key], $depth + 1)) {
                // FALSE, if child comparision fails.
                return FALSE;
            }

            // Unset key to check whether all keys of b are compared.
            unset($b[$key]);
        }

        if (count($b)) {
            // There is something in $b, that is missing in $a.
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Compares two numeric values - use delta if applieable
     * 
     * @param mixed $a First value
     * @param mixed $b Second value
     * @return bool
     */
    protected function numericComparison($a, $b)
    {
        if ($this->delta === FALSE) {
            return ($a == $b);
        } else {
            return (abs($a - $b) <= $this->delta);
        }
    }
}
?>
