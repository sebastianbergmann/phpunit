<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: ExtensionTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Extensions/TestSetup.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestSuite.php';

require_once 'PHPUnit2/Tests/Error.php';
require_once 'PHPUnit2/Tests/Failure.php';
require_once 'PHPUnit2/Tests/Success.php';
require_once 'PHPUnit2/Tests/TornDown.php';
require_once 'PHPUnit2/Tests/WasRun.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Extensions_ExtensionTest extends PHPUnit2_Framework_TestCase {
    public function testRunningErrorInTestSetup() {
/*
        $wrapper = new PHPUnit2_Extensions_TestSetup(
          new PHPUnit2_Tests_Failure
        );

        $result = $wrapper->run();

        $this->assertFalse($result->wasSuccessful());
*/
    }

    public function testRunningErrorsInTestSetup() {
/*
        $suite = new PHPUnit2_Framework_TestSuite;
        
        $suite->addTest('PHPUnit2_Tests_Error');
        $suite->addTest('PHPUnit2_Tests_Failure');

        $wrapper = new PHPUnit2_Extensions_TestSetup($suite);
        
        $result = $wrapper->run();

        $this->assertEquals(1, $result->errorCount());
        $this->assertEquals(1, $result->failureCount());
*/
    }

    public function testSetupErrorDontTearDown() {
/*
        $test    = new PHPUnit2_Tests_WasRun;
        $wrapper = new PHPUnit2_Tests_TornDown($test);

        $wrapper->run();

        $this->assertFalse($wrapper->tornDown);
*/
    }

    public function testSetupErrorInTestSetup() {
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
