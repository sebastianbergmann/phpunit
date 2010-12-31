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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework/ComparisonFailure.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class Framework_ComparisonFailureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorMessage()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('a', 'b', 'c');

        $this->assertEquals(
          "c\n--- Expected\n+++ Actual\n@@ @@\n-a\n+b",
          $failure->toString()
        );
    }

    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorStartSame()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('ba', 'bc');

        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-ba\n+bc",
          $failure->toString()
        );
    }

    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorEndSame()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('ab', 'cb');

        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-ab\n+cb",
          $failure->toString()
        );
    }

    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorStartAndEndSame()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('abc', 'adc');

        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-abc\n+adc",
          $failure->toString()
        );
    }

    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorStartSameComplete()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('ab', 'abc');

        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-ab\n+abc",
          $failure->toString()
        );
    }

    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorEndSameComplete()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('bc', 'abc');

        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-bc\n+abc",
          $failure->toString()
        );
    }

    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorOverlapingMatches()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('abc', 'abbc');

        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-abc\n+abbc",
          $failure->toString()
        );
    }

    /**
     * @covers PHPUnit_Framework_ComparisonFailure::diffEqual
     */
    public function testComparisonErrorOverlapingMatches2()
    {
        $failure = PHPUnit_Framework_ComparisonFailure::diffEqual('abcdde', 'abcde');

        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-abcdde\n+abcde",
          $failure->toString()
        );
    }
}
