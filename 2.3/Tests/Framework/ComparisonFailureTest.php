<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    CVS: $Id: ComparisonFailureTest.php,v 1.14.2.2 2005/12/17 16:04:57 sebastian Exp $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/ComparisonFailure.php';
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * 
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class Framework_ComparisonFailureTest extends PHPUnit2_Framework_TestCase {
    public function testComparisonErrorMessage() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('a', 'b', 'c');

        $this->assertEquals(
          'c expected: <a> but was: <b>',
          $failure->toString()
        );
    }

    public function testComparisonErrorStartSame() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('ba', 'bc');

        $this->assertEquals(
          'expected: <...a> but was: <...c>',
          $failure->toString()
        );
    }

    public function testComparisonErrorEndSame() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('ab', 'cb');

        $this->assertEquals(
          'expected: <a...> but was: <c...>',
          $failure->toString()
        );
    }

    public function testComparisonErrorSame() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('ab', 'ab');

        $this->assertEquals(
          'expected: <ab> but was: <ab>',
          $failure->toString()
        );
    }

    public function testComparisonErrorStartAndEndSame() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('abc', 'adc');

        $this->assertEquals(
          'expected: <...b...> but was: <...d...>',
          $failure->toString()
        );
    }

    public function testComparisonErrorStartSameComplete() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('ab', 'abc');

        $this->assertEquals(
          'expected: <...> but was: <...c>',
          $failure->toString()
        );
    }

    public function testComparisonErrorEndSameComplete() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('bc', 'abc');

        $this->assertEquals(
          'expected: <...> but was: <a...>',
          $failure->toString()
        );
    }

    public function testComparisonErrorOverlapingMatches() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('abc', 'abbc');

        $this->assertEquals(
          'expected: <......> but was: <...b...>',
          $failure->toString()
        );
    }

    public function testComparisonErrorOverlapingMatches2() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('abcdde', 'abcde');

        $this->assertEquals(
          'expected: <...d...> but was: <......>',
          $failure->toString()
        );
    }

    public function testComparisonErrorWithActualNull() {
        $failure = new PHPUnit2_Framework_ComparisonFailure('a', NULL);

        $this->assertEquals(
          'expected: <a> but was: <NULL>',
          $failure->toString()
        );
    }

    public function testComparisonErrorWithExpectedNull() {
        $failure = new PHPUnit2_Framework_ComparisonFailure(NULL, 'a');

        $this->assertEquals(
          'expected: <NULL> but was: <a>',
          $failure->toString()
        );
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
