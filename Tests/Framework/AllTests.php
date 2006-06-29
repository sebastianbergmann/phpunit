<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: AllTests.php 539 2006-02-13 16:08:42Z sb $
//

if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'PHPUnit2_Tests_Framework_AllTests::main');
}

require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/TextUI/TestRunner.php';
require_once 'PHPUnit2/Util/Filter.php';

require_once 'PHPUnit2/Tests/Framework/AssertTest.php';
require_once 'PHPUnit2/Tests/Framework/ComparisonFailureTest.php';
require_once 'PHPUnit2/Tests/Framework/SuiteTest.php';
require_once 'PHPUnit2/Tests/Framework/TestCaseTest.php';
require_once 'PHPUnit2/Tests/Framework/TestImplementorTest.php';
require_once 'PHPUnit2/Tests/Framework/TestListenerTest.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Framework_AllTests {
    public static function main() {
        PHPUnit2_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit2_Framework_TestSuite('PHPUnit Framework');

        $suite->addTestSuite('PHPUnit2_Tests_Framework_AssertTest');
        $suite->addTestSuite('PHPUnit2_Tests_Framework_ComparisonFailureTest');
        $suite->addTestSuite('PHPUnit2_Tests_Framework_SuiteTest');
        $suite->addTestSuite('PHPUnit2_Tests_Framework_TestCaseTest');
        $suite->addTestSuite('PHPUnit2_Tests_Framework_TestImplementorTest');
        $suite->addTestSuite('PHPUnit2_Tests_Framework_TestListenerTest');

        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/DoubleTestCase.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/Error.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/Failure.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/InheritedTestCase.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/NoArgTestCaseTest.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/NoTestCaseClass.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/NoTestCases.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/NotPublicTestCase.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/NotVoidTestCase.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/OneTestCase.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/OverrideTestCase.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/SetupFailure.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/Success.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/TearDownFailure.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/TornDown2.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/TornDown3.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/TornDown4.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/TornDown5.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/WasRun.php');

        return $suite;
    }
}

if (PHPUnit2_MAIN_METHOD == 'PHPUnit2_Tests_Framework_AllTests::main') {
    PHPUnit2_Tests_Framework_AllTests::main();
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
