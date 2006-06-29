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
require_once 'PHPUnit/TestResult.php';
require_once 'PHPUnit/TestSuite.php';

/**
 * PHPUnit runs a TestSuite and returns a TestResult object.
 *
 * Here is an example:
 *
 * <code>
 * <?php
 * require_once 'PHPUnit.php';
 *
 * class MathTest extends PHPUnit_TestCase {
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
 *       $this->assertTrue($this->fValue1 + $this->fValue2 == 5);
 *     }
 * }
 *
 * $suite = new PHPUnit_TestSuite();
 * $suite->addTest(new MathTest('testAdd'));
 *
 * $result = PHPUnit::run($suite);
 * print $result->toHTML();
 * ?>
 * </code>
 *
 * Alternatively, you can pass a class name to the PHPUnit_TestSuite()
 * constructor and let it automatically add all methods of that class
 * that start with 'test' to the suite:
 *
 * <code>
 * <?php
 * $suite  = new PHPUnit_TestSuite('MathTest');
 * $result = PHPUnit::run($suite);
 * print $result->toHTML();
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
class PHPUnit {
    /**
     * Runs a test(suite).
     *
     * @param  mixed
     * @return PHPUnit_TestResult
     * @access public
     */
    function &run(&$suite) {
        $result = new PHPUnit_TestResult();
        $suite->run($result);

        return $result;
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
