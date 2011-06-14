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
 * @subpackage Framework
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

/**
 * Abstract base class for comparators which compare values for equality.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
abstract class PHPUnit_Framework_Comparator
{
    /**
     * @var array
     */
    protected static $comparators = array();

    /**
     * Returns the correct comparator for comparing two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return PHPUnit_Framework_Comparator
     * @since  Method available since Release 3.0.0
     */
    public static function getInstance($expected, $actual)
    {
        foreach (self::$comparators as $comparator) {
            if ($comparator->accepts($expected, $actual)) {
                return $comparator;
            }
        }

        throw new InvalidArgumentException(sprintf('No comparator is registered for comparing the types "%s" and "%s"', gettype($expected), gettype($actual)));
    }

    /**
     * Registers a new comparator.
     *
     * This comparator will be returned by getInstance() if its accept() method
     * returns TRUE for the compared values. It has higher priority than the
     * existing comparators, meaning that its accept() method will be tested
     * before those of the other comparators.
     *
     * @param  PHPUnit_Framework_Comparator $comparator The registered comparator
     * @since  Method available since Release 3.0.0
     */
    public static function register(PHPUnit_Framework_Comparator $comparator)
    {
        array_unshift(self::$comparators, $comparator);
    }

    /**
     * Unregisters a comparator.
     *
     * This comparator will no longer be returned by getInstance().
     *
     * @param  PHPUnit_Framework_Comparator $comparator The unregistered comparator
     * @since  Method available since Release 3.0.0
     */
    public static function unregister(PHPUnit_Framework_Comparator $comparator)
    {
        if (false !== ($key = array_search($comparator, self::$comparators, true)))
        {
            unset(self::$comparators[$key]);
        }
    }

    /**
     * Returns whether the comparator can compare two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return boolean
     * @since  Method available since Release 3.0.0
     */
    abstract public function accepts($expected, $actual);

    /**
     * Asserts that two values are equal.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @param  float $delta The allowed numerical distance between two values to
     *                      consider them equal
     * @param  bool  $canonicalize If set to TRUE, arrays are sorted before
     *                             comparison
     * @param  bool  $ignoreCase If set to TRUE, upper- and lowercasing is
     *                           ignored when comparing string values
     * @throws PHPUnit_Framework_ComparisonFailure Thrown when the comparison
     *                           fails. Contains information about the
     *                           specific errors that lead to the failure.
     * @since  Method available since Release 3.0.0
     */
    abstract public function assertEquals($expected, $actual, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE);
}