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
 * @version    CVS: $Id: RepeatedTestTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Extensions/RepeatedTest.php';

require_once 'Success.php';

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
class Extensions_RepeatedTestTest extends PHPUnit2_Framework_TestCase {
    private $suite;

    public function __construct() {
        $this->suite = new PHPUnit2_Framework_TestSuite;

        $this->suite->addTest(new Success);
        $this->suite->addTest(new Success);
    }

    public function testRepeatedOnce() {
        $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, 1);
        $this->assertEquals(2, $test->countTestCases());

        $result = $test->run();
        $this->assertEquals(2, $result->runCount());
    }

    public function testRepeatedMoreThanOnce() {
        $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, 3);
        $this->assertEquals(6, $test->countTestCases());

        $result = $test->run();
        $this->assertEquals(6, $result->runCount());
    }

    public function testRepeatedZero() {
        $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, 0);
        $this->assertEquals(0, $test->countTestCases());

        $result = $test->run();
        $this->assertEquals(0, $result->runCount());
    }

    public function testRepeatedNegative() {
        try {
            $test = new PHPUnit2_Extensions_RepeatedTest($this->suite, -1);
        }

        catch (Exception $e) {
            return;
        }

        $this->fail('Should throw an Exception');
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
