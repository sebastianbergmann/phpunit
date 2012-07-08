<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
     * @var PHPUnit_Framework_ComparisonFailure
     */
    protected $lastFailure;

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
     * Evaluates the constraint for parameter $other
     *
     * If $returnResult is set to FALSE (the default), an exception is thrown
     * in case of a failure. NULL is returned otherwise.
     *
     * If $returnResult is TRUE, the result of the evaluation is returned as
     * a boolean value instead: TRUE in case of success, FALSE in case of a
     * failure.
     *
     * @param  mixed $other Value or object to evaluate.
     * @param  string $description Additional information about the test
     * @param  bool $returnResult Whether to return a result or throw an exception
     * @return mixed
     * @throws PHPUnit_Framework_ExpectationFailedException
     */
    public function evaluate($other, $description = '', $returnResult = FALSE)
    {
        $comparatorFactory = PHPUnit_Framework_ComparatorFactory::getDefaultInstance();

        try {
            $comparator = $comparatorFactory->getComparatorFor(
              $other, $this->value
            );

            $comparator->assertEquals(
              $this->value,
              $other,
              $this->delta,
              $this->canonicalize,
              $this->ignoreCase
            );
        }

        catch (PHPUnit_Framework_ComparisonFailure $f) {
            if ($returnResult) {
                return FALSE;
            }

            throw new PHPUnit_Framework_ExpectationFailedException(
              trim($description . "\n" . $f->getMessage()),
              $f
            );
        }

        return TRUE;
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

              PHPUnit_Util_Type::export($this->value),
              $delta
            );
        }
    }
}
