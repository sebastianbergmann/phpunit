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
 * @version    CVS: $Id: SuiteTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Framework/TestSuite.php';

require_once 'InheritedTestCase.php';
require_once 'NoTestCaseClass.php';
require_once 'NoTestCases.php';
require_once 'NotPublicTestCase.php';
require_once 'NotVoidTestCase.php';
require_once 'OneTestCase.php';
require_once 'OverrideTestCase.php';

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
class Framework_SuiteTest extends PHPUnit2_Framework_TestCase {
    protected $result;

    protected function setUp() {
        $this->result = new PHPUnit2_Framework_TestResult;
    }

    public static function suite() {
        $suite = new PHPUnit2_Framework_TestSuite;

        $suite->addTest(new Framework_SuiteTest('testAddTestSuite'));
        $suite->addTest(new Framework_SuiteTest('testInheritedTests'));
        $suite->addTest(new Framework_SuiteTest('testNoTestCases'));
        $suite->addTest(new Framework_SuiteTest('testNoTestCaseClass'));
        $suite->addTest(new Framework_SuiteTest('testNotExistingTestCase'));
        $suite->addTest(new Framework_SuiteTest('testNotPublicTestCase'));
        $suite->addTest(new Framework_SuiteTest('testNotVoidTestCase'));
        $suite->addTest(new Framework_SuiteTest('testOneTestCase'));
        $suite->addTest(new Framework_SuiteTest('testShadowedTests'));

        return $suite;
    }

    public function testAddTestSuite() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'OneTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(1, $this->result->runCount());
    }

    public function testInheritedTests() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'InheritedTestCase'
        );

        $suite->run($this->result);

        $this->assertTrue($this->result->wasSuccessful());
        $this->assertEquals(2, $this->result->runCount());
    }

    public function testNoTestCases() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'NoTestCases'
        );

        $suite->run($this->result);

        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertEquals(1, $this->result->runCount());
    }

    public function testNoTestCaseClass() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'NoTestCaseClass'
        );

        $suite->run($this->result);

        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(1, $this->result->runCount());
    }

    public function testNotExistingTestCase() {
        $suite = new Framework_SuiteTest('notExistingMethod');

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertEquals(1, $this->result->runCount());
    }

    public function testNotPublicTestCase() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'NotPublicTestCase'
        );

        $this->assertEquals(2, $suite->countTestCases());
    }

    public function testNotVoidTestCase() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'NotVoidTestCase'
        );

        $this->assertEquals(1, $suite->countTestCases());
    }

    public function testOneTestCase() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'OneTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertEquals(1, $this->result->runCount());
        $this->assertTrue($this->result->wasSuccessful());
    }

    public function testShadowedTests() {
        $suite = new PHPUnit2_Framework_TestSuite(
          'OverrideTestCase'
        );

        $suite->run($this->result);

        $this->assertEquals(1, $this->result->runCount());
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
