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
 * @subpackage Framework_ComparisonFailure
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Thrown when an assertion for array equality failed.
 *
 * @package    PHPUnit
 * @subpackage Framework_ComparisonFailure
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_ComparisonFailure_Array extends PHPUnit_Framework_ComparisonFailure
{
    /**
     * Returns a string describing the difference between the expected and the
     * actual array.
     *
     * @return string
     */
    public function toString()
    {
        if (!$this->identical) {
            ksort($this->expected);
            ksort($this->actual);
        }

        $diff = PHPUnit_Util_Diff::diff(
          print_r($this->expected, TRUE),
          print_r($this->actual, TRUE)
        );

        if ($diff !== FALSE) {
            return trim($diff);
        }

        // Fallback: Either diff is not available or the print_r() output for
        // the expected and the actual array are equal (but the arrays are not).

        $expectedOnly = array();
        $actualOnly   = array();
        $diff         = '';

        foreach ($this->expected as $expectedKey => $expectedValue) {
            if (!array_key_exists($expectedKey, $this->actual)) {
                $expectedOnly[] = $expectedKey;
                continue;
            }

            if ($expectedValue === $this->actual[$expectedKey]) {
                continue;
            }

            $diffObject = PHPUnit_Framework_ComparisonFailure::diffIdentical(
              $expectedValue,
              $this->actual[$expectedKey],
              sprintf(
                '%sarray key %s: ',

                $this->message,
                PHPUnit_Util_Type::toString($expectedKey)
              )
            );

            $diff .= $diffObject->toString() . "\n";
        }

        foreach ($this->actual as $actualKey => $actualValue) {
            if (!array_key_exists($actualKey, $this->expected)) {
                $actualOnly[] = $actualKey;
                continue;
            }
        }

        foreach ($expectedOnly as $expectedKey) {
            $diff .= sprintf(
              "array key %s: only in expected %s\n",

              PHPUnit_Util_Type::toString($expectedKey),
              PHPUnit_Util_Type::toString($this->expected[$expectedKey])
            );
        }

        foreach ($actualOnly as $actualKey) {
            $diff .= sprintf(
              "array key %s: only in actual %s\n",

              PHPUnit_Util_Type::toString($actualKey),
              PHPUnit_Util_Type::toString($this->actual[$actualKey])
            );
        }

        return $diff;
    }
}
