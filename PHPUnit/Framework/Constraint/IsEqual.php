<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Kore Nordmann <kn@ez.no>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Constraint that checks if one value is equal to another.
 *
 * Equality is checked with PHP's == operator, the operator is explained in
 * detail at {@url http://www.php.net/manual/en/types.comparisons.php}.
 * Two values are equal if they have the same value disregarding type.
 *
 * The expected value is passed in the constructor.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Kore Nordmann <kn@ez.no>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_Constraint_IsEqual extends PHPUnit_Framework_Constraint
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var float
     */
    protected $delta = 0;

    /**
     * @var integer
     */
    protected $maxDepth = 10;

    /**
     * @var boolean
     */
    protected $canonicalize = FALSE;

    /**
     * @var boolean
     */
    protected $ignoreCase = FALSE;

    /**
     * @param mixed   $value
     * @param float   $delta
     * @param integer $maxDepth
     * @param boolean $canonicalize
     * @param boolean $ignoreCase
     */
    public function __construct($value, $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        if (!is_numeric($delta)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'numeric');
        }

        if (!is_int($maxDepth)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'integer');
        }

        if (!is_bool($canonicalize)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(4, 'boolean');
        }

        if (!is_bool($ignoreCase)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(5, 'boolean');
        }

        $this->value        = $value;
        $this->delta        = $delta;
        $this->maxDepth     = $maxDepth;
        $this->canonicalize = $canonicalize;
        $this->ignoreCase   = $ignoreCase;
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
            if ($this->value instanceof DOMDocument) {
                $value = $this->domToText($this->value);
            } else {
                $value = $this->value;
            }

            if ($other instanceof DOMDocument) {
                $other = $this->domToText($other);
            }

            throw new PHPUnit_Framework_ExpectationFailedException(
              $failureDescription,
              PHPUnit_Framework_ComparisonFailure::diffEqual($value, $other),
              $description
            );
        } else {
            throw new PHPUnit_Framework_ExpectationFailedException(
              $failureDescription,
              NULL
            );
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
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
                  ' with delta <%F>',

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
     * Perform the actual recursive comparison of two values
     *
     * @param mixed $a First value
     * @param mixed $b Second value
     * @param int $depth Depth
     * @return bool
     */
    protected function recursiveComparison($a, $b, $depth = 0)
    {
        if ($a === $b) {
            return TRUE;
        }

        if ($depth >= $this->maxDepth) {
            return TRUE;
        }

        if (is_numeric($a) XOR is_numeric($b)) {
            return FALSE;
        }

        if (is_array($a) XOR is_array($b)) {
            return FALSE;
        }

        if (is_object($a) XOR is_object($b)) {
            return FALSE;
        }

        if ($a instanceof SplObjectStorage XOR $b instanceof SplObjectStorage) {
            return FALSE;
        }

        if ($a instanceof SplObjectStorage) {
            foreach ($a as $object) {
                if (!$b->contains($object)) {
                    return FALSE;
                }
            }

            foreach ($b as $object) {
                if (!$a->contains($object)) {
                    return FALSE;
                }
            }

            return TRUE;
        }

        if ($a instanceof DOMDocument || $b instanceof DOMDocument) {
            if (!$a instanceof DOMDocument) {
                $_a = new DOMDocument;
                $_a->preserveWhiteSpace = FALSE;
                $_a->loadXML($a);
                $a = $_a;
                unset($_a);
            }

            if (!$b instanceof DOMDocument) {
                $_b = new DOMDocument;
                $_b->preserveWhiteSpace = FALSE;
                $_b->loadXML($b);
                $b = $_b;
                unset($_b);
            }

            return $a->C14N() == $b->C14N();
        }

        if (is_object($a) && is_object($b) &&
           (get_class($a) !== get_class($b))) {
            return FALSE;
        }

        // Normal comparison for scalar values.
        if ((!is_array($a) && !is_object($a)) ||
            (!is_array($b) && !is_object($b))) {
            if (is_numeric($a) && is_numeric($b)) {
                // Optionally apply delta on numeric values.
                return $this->numericComparison($a, $b);
            }

            if (is_string($a) && is_string($b)) {
                if ($this->canonicalize && PHP_EOL != "\n") {
                    $a = str_replace(PHP_EOL, "\n", $a);
                    $b = str_replace(PHP_EOL, "\n", $b);
                }

                if ($this->ignoreCase) {
                    $a = strtolower($a);
                    $b = strtolower($b);
                }
            }

            return ($a == $b);
        }

        if (is_object($a)) {
            $isMock = $a instanceof PHPUnit_Framework_MockObject_MockObject;
            $a      = (array)$a;
            $b      = (array)$b;

            if ($isMock) {
                unset($a["\0*\0invocationMocker"]);

                if (isset($b["\0*\0invocationMocker"])) {
                    unset($b["\0*\0invocationMocker"]);
                }
            }
        }

        if ($this->canonicalize) {
            sort($a);
            sort($b);
        }

        $keysInB = array_flip(array_keys($b));

        foreach ($a as $key => $v) {
            if (!isset($keysInB[$key])) {
                // Abort on missing key in $b.
                return FALSE;
            }

            if (!$this->recursiveComparison($a[$key], $b[$key], $depth + 1)) {
                // FALSE, if child comparison fails.
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
     * Compares two numeric values - use delta if applicable.
     *
     * @param mixed $a
     * @param mixed $b
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

    /**
     * Returns the normalized, whitespace-cleaned, and indented textual
     * representation of a DOMDocument.
     *
     * @param DOMDocument $document
     * @return string
     */
    protected function domToText(DOMDocument $document)
    {
        $document->formatOutput = TRUE;
        $document->normalizeDocument();

        return $document->saveXML();
    }
}
