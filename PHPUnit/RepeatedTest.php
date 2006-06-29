<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 4
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/PHPUnit
 * @since      File available since Release 1.0.0
 */

require_once 'PHPUnit/TestDecorator.php';

/**
 * A Decorator that runs a test repeatedly.
 *
 * Here is an example:
 *
 * <code>
 * <?php
 * require_once 'PHPUnit.php';
 * require_once 'PHPUnit/RepeatedTest.php';
 *
 * class MathTest extends PHPUnit_TestCase {
 *     var $fValue1;
 *     var $fValue2;
 *
 *     function MathTest($name) {
 *         $this->PHPUnit_TestCase($name);
 *     }
 *
 *     function setUp() {
 *         $this->fValue1 = 2;
 *         $this->fValue2 = 3;
 *     }
 *
 *     function testAdd() {
 *         $this->assertTrue($this->fValue1 + $this->fValue2 == 5);
 *     }
 * }
 *
 * $suite = new PHPUnit_TestSuite;
 *
 * $suite->addTest(
 *   new PHPUnit_RepeatedTest(
 *     new MathTest('testAdd'),
 *     10
 *   )
 * );
 *
 * $result = PHPUnit::run($suite);
 * print $result->toString();
 * ?>
 * </code>
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_RepeatedTest extends PHPUnit_TestDecorator {
    /**
     * @var    integer
     * @access private
     */
    var $_timesRepeat = 1;

    /**
     * Constructor.
     *
     * @param  object
     * @param  integer
     * @access public
     */
    function PHPUnit_RepeatedTest(&$test, $timesRepeat = 1) {
        $this->PHPUnit_TestDecorator($test);
        $this->_timesRepeat = $timesRepeat;
    }

    /**
     * Counts the number of test cases that
     * will be run by this test.
     *
     * @return integer
     * @access public
     */
    function countTestCases() {
        return $this->_timesRepeat * $this->_test->countTestCases();
    }

    /**
     * Runs the decorated test and collects the
     * result in a TestResult.
     *
     * @param  object
     * @access public
     * @abstract
     */
    function run(&$result) {
        for ($i = 0; $i < $this->_timesRepeat; $i++) {
            $this->_test->run($result);
        }
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
