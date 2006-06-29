<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit                                                        |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2003 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id$
//

/**
 * A Listener for test progress.
 *
 * Here is an example:
 *
 *   <?php
 *   require_once 'PHPUnit.php';
 *   require_once 'PHPUnit/TestListener.php';
 *
 *   class MathTest extends PHPUnit_TestCase {
 *     var $fValue1;
 *     var $fValue2;
 *
 *     function MathTest($name) {
 *       $this->PHPUnit_TestCase($name);
 *     }
 *
 *     function setUp() {
 *       $this->fValue1 = 2;
 *       $this->fValue2 = 3;
 *     }
 *
 *     function testAdd() {
 *       $this->assertTrue($this->fValue1 + $this->fValue2 == 4);
 *     }
 *   }
 *
 *   class MyListener extends PHPUnit_TestListener {
 *     function addError(&$test, &$t) {
 *       echo "MyListener::addError() called.\n";
 *     }
 *
 *     function addFailure(&$test, &$t) {
 *       echo "MyListener::addFailure() called.\n";
 *     }
 *
 *     function endTest(&$test) {
 *       echo "MyListener::endTest() called.\n";
 *     }
 *
 *     function startTest(&$test) {
 *       echo "MyListener::startTest() called.\n";
 *     }
 *   }
 *
 *   $suite = new PHPUnit_TestSuite;
 *   $suite->addTest(new MathTest('testAdd'));
 *
 *   $result = new PHPUnit_TestResult;
 *   $result->addListener(new MyListener);
 *
 *   $suite->run($result);
 *   echo $result->toString();
 *   ?>
 *
 * @package PHPUnit
 * @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
 *          Based upon JUnit, see http://www.junit.org/ for details.
 */
class PHPUnit_TestListener {
    /**
    * An error occurred.
    *
    * @param  object
    * @param  object
    * @access public
    * @abstract
    */
    function addError(&$test, &$t) { /*abstract */ }

    /**
    * A failure occurred.
    *
    * @param  object
    * @param  object
    * @access public
    * @abstract
    */
    function addFailure(&$test, &$t) { /*abstract */ }

    /**
    * A test ended.
    *
    * @param  object
    * @access public
    * @abstract
    */
    function endTest(&$test) { /*abstract */ }

    /**
    * A test started.
    *
    * @param  object
    * @access public
    * @abstract
    */
    function startTest(&$test) { /*abstract */ }
}
?>
