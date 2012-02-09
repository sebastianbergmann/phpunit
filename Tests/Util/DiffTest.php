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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
class Util_DiffTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorMessage()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-a\n+b\n",
          PHPUnit_Util_Diff::diff('a', 'b')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorMessage_toArray()
    {
        $diff = array();
        $diff[] = array('a', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('b', PHPUnit_Util_DIFF::ADDED);
        
        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('a', 'b')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorStartSame()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-ba\n+bc\n",
          PHPUnit_Util_Diff::diff('ba', 'bc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorStartSame_toArray()
    {
        $diff = array();
        $diff[] = array('ba', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('bc', PHPUnit_Util_DIFF::ADDED);
        
        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('ba', 'bc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorEndSame()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-ab\n+cb\n",
          PHPUnit_Util_Diff::diff('ab', 'cb')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorEndSame_toArray()
    {
        $diff = array();
        $diff[] = array('ab', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('cb', PHPUnit_Util_DIFF::ADDED);
        
        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('ab', 'cb')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorStartAndEndSame()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-abc\n+adc\n",
          PHPUnit_Util_Diff::diff('abc', 'adc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorStartAndEndSame_toArray()
    {
        $diff = array();
        $diff[] = array('abc', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('adc', PHPUnit_Util_DIFF::ADDED);
        
        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('abc', 'adc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorStartSameComplete()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-ab\n+abc\n",
          PHPUnit_Util_Diff::diff('ab', 'abc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorStartSameComplete_toArray()
    {
        $diff = array();
        $diff[] = array('ab', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('abc', PHPUnit_Util_DIFF::ADDED);
      
        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('ab', 'abc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorEndSameComplete()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-bc\n+abc\n",
          PHPUnit_Util_Diff::diff('bc', 'abc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorEndSameComplete_toArray()
    {
        $diff = array();
        $diff[] = array('bc', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('abc', PHPUnit_Util_DIFF::ADDED);
        
        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('bc', 'abc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorOverlapingMatches()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-abc\n+abbc\n",
          PHPUnit_Util_Diff::diff('abc', 'abbc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorOverlapingMatches_toArray()
    {
        $diff = array();
        $diff[] = array('abc', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('abbc', PHPUnit_Util_DIFF::ADDED);

        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('abc', 'abbc')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diff
     */
    public function testComparisonErrorOverlapingMatches2()
    {
        $this->assertEquals(
          "--- Expected\n+++ Actual\n@@ @@\n-abcdde\n+abcde\n",
          PHPUnit_Util_Diff::diff('abcdde', 'abcde')
        );
    }

    /**
     * @covers PHPUnit_Util_Diff::diffToArray
     */
    public function testComparisonErrorOverlapingMatches2_toArray()
    {
        $diff = array();
        $diff[] = array('abcdde', PHPUnit_Util_DIFF::REMOVED);
        $diff[] = array('abcde', PHPUnit_Util_DIFF::ADDED);

        $this->assertEquals(
          $diff,
          PHPUnit_Util_Diff::diffToArray('abcdde', 'abcde')
        );
    }
}
