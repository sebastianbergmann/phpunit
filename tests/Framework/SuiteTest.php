<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'BeforeAndAfterTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'BeforeClassAndAfterClassTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'TestWithTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'BeforeClassWithOnlyDataProviderTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'DataProviderSkippedTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'DataProviderDependencyTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'DataProviderIncompleteTest.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'InheritedTestCase.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NoTestCaseClass.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NoTestCases.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NotPublicTestCase.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'NotVoidTestCase.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'OverrideTestCase.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'RequirementsClassBeforeClassHookTest.php';

class Framework_SuiteTest extends TestCase
{
    protected $result;

    protected function setUp()
    {
        $this->result = new TestResult;
    }

    public static function suite()
    {
        $suite = new TestSuite;

        $suite->addTest(new self('testAddTestSuite'));
        $suite->addTest(new self('testInheritedTests'));
        $suite->addTest(new self('testNoTestCases'));
        $suite->addTest(new self('testNoTestCaseClass'));
        $suite->addTest(new self('testNotExistingTestCase'));
        $suite->addTest(new self('testNotPublicTestCase'));
        $suite->addTest(new self('testNotVoidTestCase'));
        $suite->addTest(new self('testOneTestCase'));
        $suite->addTest(new self('testShadowedTests'));
        $suite->addTest(new self('testBeforeClassAndAfterClassAnnotations'));
        $suite->addTest(new self('testBeforeClassWithDataProviders'));
        $suite->addTest(new self('testBeforeAnnotation'));
        $suite->addTest(new self('testTestWithAnnotation'));
        $suite->addTest(new self('testSkippedTestDataProvider'));
        $suite->addTest(new self('testTestDataProviderDependency'));
        $suite->addTest(new self('testIncompleteTestDataProvider'));
        $suite->addTest(new self('testRequirementsBeforeClassHook'));
        $suite->addTest(new self('testDontSkipInheritedClass'));

        return $suite;
    }

    public function testAddTestSuite()
    {
        $suite = new TestSuite(OneTestCase::class);

        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testInheritedTests()
    {
        $suite = new TestSuite(InheritedTestCase::class);

        $suite->run($this->result);

        $this->assertTrue($this->result->wasSuccessful());
        $this->assertCount(2, $this->result);
    }

    public function testNoTestCases()
    {
        $suite = new TestSuite(NoTestCases::class);

        $suite->run($this->result);

        $this->assertTrue(!$this->result->wasSuccessful());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertEquals(1, $this->result->warningCount());
        $this->assertCount(1, $this->result);
    }

    public function testNoTestCaseClass()
    {
        $this->expectException(PHPUnit\Framework\Exception::class);

        new TestSuite(NoTestCaseClass::class);
    }

    public function testNotExistingTestCase()
    {
        $suite = new self('notExistingMethod');

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->failureCount());
        $this->assertCount(1, $this->result);
    }

    public function testNotPublicTestCase()
    {
        $suite = new TestSuite(NotPublicTestCase::class);

        $this->assertCount(2, $suite);
    }

    public function testNotVoidTestCase()
    {
        $suite = new TestSuite(NotVoidTestCase::class);

        $this->assertCount(1, $suite);
    }

    public function testOneTestCase()
    {
        $suite = new TestSuite(OneTestCase::class);

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertCount(1, $this->result);
        $this->assertTrue($this->result->wasSuccessful());
    }

    public function testShadowedTests()
    {
        $suite = new TestSuite(OverrideTestCase::class);

        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testBeforeClassAndAfterClassAnnotations()
    {
        $suite = new TestSuite(BeforeClassAndAfterClassTest::class);

        BeforeClassAndAfterClassTest::resetProperties();
        $suite->run($this->result);

        $this->assertEquals(1, BeforeClassAndAfterClassTest::$beforeClassWasRun, '@beforeClass method was not run once for the whole suite.');
        $this->assertEquals(1, BeforeClassAndAfterClassTest::$afterClassWasRun, '@afterClass method was not run once for the whole suite.');
    }

    public function testBeforeClassWithDataProviders()
    {
        $suite = new TestSuite(BeforeClassWithOnlyDataProviderTest::class);

        BeforeClassWithOnlyDataProviderTest::resetProperties();
        $suite->run($this->result);

        $this->assertTrue(BeforeClassWithOnlyDataProviderTest::$setUpBeforeClassWasCalled, 'setUpBeforeClass method was not run.');
        $this->assertTrue(BeforeClassWithOnlyDataProviderTest::$beforeClassWasCalled, '@beforeClass method was not run.');
    }

    public function testBeforeAnnotation()
    {
        $test = new TestSuite(BeforeAndAfterTest::class);

        BeforeAndAfterTest::resetProperties();
        $test->run();

        $this->assertEquals(2, BeforeAndAfterTest::$beforeWasRun);
        $this->assertEquals(2, BeforeAndAfterTest::$afterWasRun);
    }

    public function testTestWithAnnotation()
    {
        $test = new TestSuite(TestWithTest::class);

        BeforeAndAfterTest::resetProperties();
        $result = $test->run();

        $this->assertCount(4, $result->passed());
    }

    public function testSkippedTestDataProvider()
    {
        $suite = new TestSuite(DataProviderSkippedTest::class);

        $suite->run($this->result);

        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testTestDataProviderDependency()
    {
        $suite = new PHPUnit\Framework\TestSuite(DataProviderDependencyTest::class);

        $suite->run($this->result);

        $skipped           = $this->result->skipped();
        $lastSkippedResult = array_pop($skipped);
        $message           = $lastSkippedResult->thrownException()->getMessage();

        $this->assertContains('Test for DataProviderDependencyTest::testDependency skipped by data provider', $message);
    }

    public function testIncompleteTestDataProvider()
    {
        $suite = new TestSuite(DataProviderIncompleteTest::class);

        $suite->run($this->result);

        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->notImplementedCount());
    }

    public function testRequirementsBeforeClassHook()
    {
        $suite = new TestSuite(RequirementsClassBeforeClassHookTest::class);

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testDontSkipInheritedClass()
    {
        $suite = new TestSuite(
            'DontSkipInheritedClass'
        );

        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'Inheritance' . DIRECTORY_SEPARATOR;

        $suite->addTestFile($dir . 'InheritanceA.php');
        $suite->addTestFile($dir . 'InheritanceB.php');
        $result = $suite->run();
        $this->assertCount(2, $result);
    }
}
