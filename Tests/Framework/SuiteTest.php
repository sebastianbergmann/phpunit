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
// $Id: SuiteTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Framework/TestSuite.php';

require_once 'PHPUnit2/Tests/InheritedTestCase.php';
require_once 'PHPUnit2/Tests/NoTestCaseClass.php';
require_once 'PHPUnit2/Tests/NoTestCases.php';
require_once 'PHPUnit2/Tests/NotPublicTestCase.php';
require_once 'PHPUnit2/Tests/NotVoidTestCase.php';
require_once 'PHPUnit2/Tests/OneTestCase.php';
require_once 'PHPUnit2/Tests/OverrideTestCase.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Framework_SuiteTest extends PHPUnit2_Framework_TestCase {
    protected $result;

    protected function setUp() {
        $this->result = new PHPUnit2_Framework_TestResult;
    }

    public static function suite() {
        $suite = new PHPUnit2_Framework_TestSuite;

        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testAddTestSuite'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testInheritedTests'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testNoTestCases'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testNoTestCaseClass'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testNotExistingTestCase'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testNotPublicTestCase'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testNotVoidTestCase'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testOneTestCase'));
        $suite->addTest(new PHPUnit2_Tests_Framework_SuiteTest('testShadowedTests'));

        return $suite;
    }

    public function testAddTestSuite() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_OneTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(1, $this->result->runCount());
    }

    public function testInheritedTests() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_InheritedTestCase'
        );

        $suite->run($this->result);

        $this->assertTrue($this->result->wasSuccessful());
        $this->assertEquals(2, $this->result->runCount());
    }

    public function testNoTestCases() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_NoTestCases'
        );

        $suite->run($this->result);

        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertEquals(1, $this->result->runCount());
    }

    public function testNoTestCaseClass() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_NoTestCaseClass'
        );

        $suite->run($this->result);

        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(1, $this->result->runCount());
    }

    public function testNotExistingTestCase() {
        $suite = new PHPUnit2_Tests_Framework_SuiteTest('notExistingMethod');

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertEquals(1, $this->result->runCount());
    }

    public function testNotPublicTestCase() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_NotPublicTestCase'
        );

        $this->assertEquals(2, $suite->countTestCases());
    }

    public function testNotVoidTestCase() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_NotVoidTestCase'
        );

        $this->assertEquals(1, $suite->countTestCases());
    }

    public function testOneTestCase() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_OneTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertEquals(1, $this->result->runCount());
        $this->assertTrue($this->result->wasSuccessful());
    }

    public function testShadowedTests() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'PHPUnit2_Tests_OverrideTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(1, $this->result->runCount());
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
