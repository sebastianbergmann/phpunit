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
 * @version    CVS: $Id: ExtensionTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Extensions/TestSetup.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestSuite.php';

require_once 'Error.php';
require_once 'Failure.php';
require_once 'Success.php';
require_once 'TornDown6.php';
require_once 'WasRun.php';

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
class Extensions_ExtensionTest extends PHPUnit2_Framework_TestCase {
    public function testRunningErrorInTestSetup() {
        $wrapper = new PHPUnit2_Extensions_TestSetup(new Failure);
        $result  = $wrapper->run();

        $this->assertFalse($result->wasSuccessful());
    }

    public function testRunningErrorsInTestSetup() {
        $suite = new PHPUnit2_Framework_TestSuite;
        $suite->addTest(new Error);
        $suite->addTest(new Failure);

        $wrapper = new PHPUnit2_Extensions_TestSetup($suite);
        $result  = $wrapper->run();

        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
    }

    public function testSetupErrorDontTearDown() {
/*
        $wrapper = new TornDown6(new WasRun);
        $result  = $wrapper->run();

        $this->assertFalse($wrapper->tornDown);
*/
    }

    public function testSetupErrorInTestSetup() {
/*
        $test    = new WasRun;
        $wrapper = new TornDown6($test);
        $result  = $wrapper->run();

        $this->assertFalse($test->wasRun);
        $this->assertFalse($result->wasSuccessful());
*/
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
