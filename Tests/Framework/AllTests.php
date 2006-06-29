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
 * @version    CVS: $Id: AllTests.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'Framework_AllTests::main');
}

require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/TextUI/TestRunner.php';
require_once 'PHPUnit2/Util/Filter.php';

require_once 'Framework/AssertTest.php';
require_once 'Framework/ComparisonFailureTest.php';
require_once 'Framework/SuiteTest.php';
require_once 'Framework/TestCaseTest.php';
require_once 'Framework/TestImplementorTest.php';
require_once 'Framework/TestListenerTest.php';

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
class Framework_AllTests {
    public static function main() {
        PHPUnit2_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit2_Framework_TestSuite('PHPUnit Framework');

        $suite->addTestSuite('Framework_AssertTest');
        $suite->addTestSuite('Framework_ComparisonFailureTest');
        $suite->addTestSuite('Framework_SuiteTest');
        $suite->addTestSuite('Framework_TestCaseTest');
        $suite->addTestSuite('Framework_TestImplementorTest');
        $suite->addTestSuite('Framework_TestListenerTest');

        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/Assert.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/AssertionFailedError.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/ComparisonFailure.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/IncompleteTest.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/IncompleteTestError.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/Test.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/TestCase.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/TestFailure.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/TestListener.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/TestResult.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/TestSuite.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Framework/Warning.php');

        return $suite;
    }
}

if (PHPUnit2_MAIN_METHOD == 'Framework_AllTests::main') {
    Framework_AllTests::main();
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
