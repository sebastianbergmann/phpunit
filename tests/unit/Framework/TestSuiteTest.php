<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

/**
 * @small
 */
final class TestSuiteTest extends TestCase
{
    /**
     * @var TestResult
     */
    private $result;

    protected function setUp(): void
    {
        $this->result = new TestResult;
    }

    protected function tearDown(): void
    {
        $this->result = null;
    }

    /**
     * @testdox TestSuite can be created with name of existing non-TestCase class
     */
    public function testSuiteNameCanBeSameAsExistingNonTestClassName(): void
    {
        $suite = new TestSuite('stdClass');
        $suite->addTestSuite(\OneTestCase::class);
        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testAddTestSuite(): void
    {
        $suite = new TestSuite(\OneTestCase::class);

        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testInheritedTests(): void
    {
        $suite = new TestSuite(\InheritedTestCase::class);

        $suite->run($this->result);

        $this->assertTrue($this->result->wasSuccessful());
        $this->assertCount(2, $this->result);
    }

    public function testNoTestCases(): void
    {
        $suite = new TestSuite(\NoTestCases::class);

        $suite->run($this->result);

        $this->assertNotTrue($this->result->wasSuccessful());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertEquals(1, $this->result->warningCount());
        $this->assertCount(1, $this->result);
    }

    public function testNotPublicTestCase(): void
    {
        $suite = new TestSuite(\NotPublicTestCase::class);

        $this->assertCount(2, $suite);
    }

    public function testNotVoidTestCase(): void
    {
        $suite = new TestSuite(\NotVoidTestCase::class);

        $this->assertCount(1, $suite);
    }

    public function testOneTestCase(): void
    {
        $suite = new TestSuite(\OneTestCase::class);

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertCount(1, $this->result);
        $this->assertTrue($this->result->wasSuccessful());
    }

    public function testShadowedTests(): void
    {
        $suite = new TestSuite(\OverrideTestCase::class);

        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testBeforeClassAndAfterClassAnnotations(): void
    {
        $suite = new TestSuite(\BeforeClassAndAfterClassTest::class);

        \BeforeClassAndAfterClassTest::resetProperties();
        $suite->run($this->result);

        $this->assertEquals(1, \BeforeClassAndAfterClassTest::$beforeClassWasRun, '@beforeClass method was not run once for the whole suite.');
        $this->assertEquals(1, \BeforeClassAndAfterClassTest::$afterClassWasRun, '@afterClass method was not run once for the whole suite.');
    }

    public function testBeforeClassWithDataProviders(): void
    {
        $suite = new TestSuite(\BeforeClassWithOnlyDataProviderTest::class);

        \BeforeClassWithOnlyDataProviderTest::resetProperties();
        $suite->run($this->result);

        $this->assertTrue(\BeforeClassWithOnlyDataProviderTest::$setUpBeforeClassWasCalled, 'setUpBeforeClass method was not run.');
        $this->assertTrue(\BeforeClassWithOnlyDataProviderTest::$beforeClassWasCalled, '@beforeClass method was not run.');
    }

    public function testBeforeAnnotation(): void
    {
        $test = new TestSuite(\BeforeAndAfterTest::class);

        \BeforeAndAfterTest::resetProperties();
        $test->run();

        $this->assertEquals(2, \BeforeAndAfterTest::$beforeWasRun);
        $this->assertEquals(2, \BeforeAndAfterTest::$afterWasRun);
    }

    public function testTestWithAnnotation(): void
    {
        $test = new TestSuite(\TestWithTest::class);

        \BeforeAndAfterTest::resetProperties();
        $result = $test->run();

        $this->assertCount(4, $result->passed());
    }

    public function testSkippedTestDataProvider(): void
    {
        $suite = new TestSuite(\DataProviderSkippedTest::class);

        $suite->run($this->result);

        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testItErrorsOnlyOnceOnHookException(): void
    {
        $suite = new TestSuite(\TestCaseWithExceptionInHook::class);

        $suite->run($this->result);

        $this->assertEquals(2, $this->result->count());
        $this->assertEquals(1, $this->result->errorCount());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testTestDataProviderDependency(): void
    {
        $suite = new TestSuite(\DataProviderDependencyTest::class);

        $suite->run($this->result);

        $skipped           = $this->result->skipped();
        $lastSkippedResult = \array_pop($skipped);
        $message           = $lastSkippedResult->thrownException()->getMessage();

        $this->assertStringContainsString('Test for DataProviderDependencyTest::testDependency skipped by data provider', $message);
    }

    public function testIncompleteTestDataProvider(): void
    {
        $suite = new TestSuite(\DataProviderIncompleteTest::class);

        $suite->run($this->result);

        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->notImplementedCount());
    }

    public function testRequirementsBeforeClassHook(): void
    {
        $suite = new TestSuite(\RequirementsClassBeforeClassHookTest::class);

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testDoNotSkipInheritedClass(): void
    {
        $suite = new TestSuite(
            'DontSkipInheritedClass'
        );

        $dir = TEST_FILES_PATH . \DIRECTORY_SEPARATOR . 'Inheritance' . \DIRECTORY_SEPARATOR;

        $suite->addTestFile($dir . 'InheritanceA.php');
        $suite->addTestFile($dir . 'InheritanceB.php');

        $result = $suite->run();

        $this->assertCount(2, $result);
    }

    /**
     * @testdox Handles exceptions in tearDownAfterClass()
     */
    public function testTearDownAfterClassInTestSuite(): void
    {
        $suite = new TestSuite(\ExceptionInTearDownAfterClassTest::class);
        $suite->run($this->result);

        $this->assertSame(3, $this->result->count());
        $this->assertCount(1, $this->result->failures());

        $failure = $this->result->failures()[0];

        $this->assertSame(
            'Exception in ExceptionInTearDownAfterClassTest::tearDownAfterClass' . \PHP_EOL .
            'throw Exception in tearDownAfterClass()',
            $failure->thrownException()->getMessage()
        );
    }
}
