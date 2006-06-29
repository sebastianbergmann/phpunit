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

require_once 'PHPUnit/TestCase.php';
require_once 'PHPUnit/TestSuite.php';

if (!function_exists('is_a')) {
    require_once 'PHP/Compat/Function/is_a.php';
}

/**
 * A Decorator for Tests.
 *
 * Use TestDecorator as the base class for defining new
 * test decorators. Test decorator subclasses can be introduced
 * to add behaviour before or after a test is run.
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
class PHPUnit_TestDecorator {
    /**
     * The Test to be decorated.
     *
     * @var    object
     * @access protected
     */
    var $_test = NULL;

    /**
     * Constructor.
     *
     * @param  object
     * @access public
     */
    function PHPUnit_TestDecorator(&$test) {
        if (is_object($test) &&
            (is_a($test, 'PHPUnit_TestCase') ||
             is_a($test, 'PHPUnit_TestSuite'))) {

            $this->_test = &$test;
        }
    }

    /**
     * Runs the test and collects the
     * result in a TestResult.
     *
     * @param  object
     * @access public
     */
    function basicRun(&$result) {
        $this->_test->run($result);
    }

    /**
     * Counts the number of test cases that
     * will be run by this test.
     *
     * @return integer
     * @access public
     */
    function countTestCases() {
        return $this->_test->countTestCases();
    }

    /**
     * Returns the test to be run.
     *
     * @return object
     * @access public
     */
    function &getTest() {
        return $this->_test;
    }

    /**
     * Runs the decorated test and collects the
     * result in a TestResult.
     *
     * @param  object
     * @access public
     * @abstract
     */
    function run(&$result) { /* abstract */ }

    /**
     * Returns a string representation of the test.
     *
     * @return string
     * @access public
     */
    function toString() {
        return $this->_test->toString();
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
