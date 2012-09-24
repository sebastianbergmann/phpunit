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
 * @subpackage Framework
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

/**
 * Compares SplObjectStorage instances for equality.
 *
 * @package    PHPUnit
 * @subpackage Framework_Comparator
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
class PHPUnit_Framework_Comparator_SplObjectStorage extends PHPUnit_Framework_Comparator
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return boolean
     */
    public function accepts($expected, $actual)
    {
        return $expected instanceof SplObjectStorage && $actual instanceof SplObjectStorage;
    }

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
     */
    public function assertEquals($expected, $actual, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        foreach ($actual as $object) {
            if (!$expected->contains($object)) {
                throw new PHPUnit_Framework_ComparisonFailure(
                  $expected,
                  $actual,
                  PHPUnit_Util_Type::export($expected),
                  PHPUnit_Util_Type::export($actual),
                  FALSE,
                  'Failed asserting that two objects are equal.'
                );
            }
        }

        foreach ($expected as $object) {
            if (!$actual->contains($object)) {
                throw new PHPUnit_Framework_ComparisonFailure(
                  $expected,
                  $actual,
                  PHPUnit_Util_Type::export($expected),
                  PHPUnit_Util_Type::export($actual),
                  FALSE,
                  'Failed asserting that two objects are equal.'
                );
            }
        }
    }
}
