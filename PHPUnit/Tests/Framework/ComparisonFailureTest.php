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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework/ComparisonFailure.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class Framework_ComparisonFailureTest extends PHPUnit_Framework_TestCase
{
    public function testComparisonErrorMessage()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('a', 'b', 'c');

        $this->assertEquals(
          "c expected string <a>\n  difference      <x>\n  got string      <b>",
          $failure->toString()
        );
    }

    public function testComparisonErrorStartSame()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('ba', 'bc');

        $this->assertEquals(
          "expected string <ba>\ndifference      < x>\ngot string      <bc>",
          $failure->toString()
        );
    }

    public function testComparisonErrorEndSame()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('ab', 'cb');

        $this->assertEquals(
          "expected string <ab>\ndifference      <x>\ngot string      <cb>",
          $failure->toString()
        );
    }

    public function testComparisonErrorStartAndEndSame()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('abc', 'adc');

        $this->assertEquals(
          "expected string <abc>\ndifference      < x>\ngot string      <adc>",
          $failure->toString()
        );
    }

    public function testComparisonErrorStartSameComplete()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('ab', 'abc');

        $this->assertEquals(
          "expected string <ab>\ndifference      <  ?>\ngot string      <abc>",
          $failure->toString()
        );
    }

    public function testComparisonErrorEndSameComplete()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('bc', 'abc');

        $this->assertEquals(
          "expected string <bc>\ndifference      <xx?>\ngot string      <abc>",
          $failure->toString()
        );
    }

    public function testComparisonErrorOverlapingMatches()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('abc', 'abbc');

        $this->assertEquals(
          "expected string <abc>\ndifference      <  x?>\ngot string      <abbc>",
          $failure->toString()
        );
    }

    public function testComparisonErrorOverlapingMatches2()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('abcdde', 'abcde');

        $this->assertEquals(
          "expected string <abcdde>\ndifference      <    x?>\ngot string      <abcde>",
          $failure->toString()
        );
    }
}
?>
