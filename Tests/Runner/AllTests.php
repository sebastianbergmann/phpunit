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
    define('PHPUnit2_MAIN_METHOD', 'PHPUnit2_Tests_Runner_AllTests::main');
}

require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/TextUI/TestRunner.php';
require_once 'PHPUnit2/Util/Filter.php';

require_once 'PHPUnit2/Tests/Runner/BaseTestRunnerTest.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Runner_AllTests {
    public static function main() {
        PHPUnit2_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit2_Framework_TestSuite('PHPUnit Runner');

        $suite->addTestSuite('PHPUnit2_Tests_Runner_BaseTestRunnerTest');

        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Runner/BaseTestRunner.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Runner/IncludePathTestCollector.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Runner/StandardTestSuiteLoader.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Runner/TestCollector.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Runner/TestRunListener.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Runner/TestSuiteLoader.php');
        PHPUnit2_Util_Filter::removeFileFromFilter('PHPUnit2/Runner/Version.php');

        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/MockRunner.php');
        PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2/Tests/NonStatic.php');

        return $suite;
    }
}

if (PHPUnit2_MAIN_METHOD == 'PHPUnit2_Tests_Runner_AllTests::main') {
    PHPUnit2_Tests_Runner_AllTests::main();
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
