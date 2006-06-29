<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: ComparisonFailureTest.php 539 2006-02-13 16:08:42Z sb $
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
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
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
