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

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use function array_pop;
use DataProviderDependencyTest;
use DependencyFailureTest;
use DependencyOnClassTest;
use DependencySuccessTest;
use MultiDependencyTest;
use PHPUnit\TestFixture\BeforeAndAfterTest;
use PHPUnit\TestFixture\BeforeClassAndAfterClassTest;
use PHPUnit\TestFixture\BeforeClassWithOnlyDataProviderTest;
use PHPUnit\TestFixture\DataProviderIncompleteTest;
use PHPUnit\TestFixture\DataProviderSkippedTest;
use PHPUnit\TestFixture\DoubleTestCase;
use PHPUnit\TestFixture\ExceptionInTearDownAfterClassTest;
use PHPUnit\TestFixture\InheritedTestCase;
use PHPUnit\TestFixture\NoTestCases;
use PHPUnit\TestFixture\NotPublicTestCase;
use PHPUnit\TestFixture\NotVoidTestCase;
use PHPUnit\TestFixture\OneTestCase;
use PHPUnit\TestFixture\OverrideTestCase;
use PHPUnit\TestFixture\PreConditionAndPostConditionTest;
use PHPUnit\TestFixture\RequirementsClassBeforeClassHookTest;
use PHPUnit\TestFixture\Success;
use PHPUnit\TestFixture\TestCaseWithExceptionInHook;
use PHPUnit\TestFixture\TestWithTest;

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
        $suite->addTestSuite(OneTestCase::class);
        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testAddTestSuite(): void
    {
        $suite = new TestSuite(OneTestCase::class);

        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testInheritedTests(): void
    {
        $suite = new TestSuite(InheritedTestCase::class);

        $suite->run($this->result);

        $this->assertTrue($this->result->wasSuccessful());
        $this->assertCount(2, $this->result);
    }

    public function testNoTestCases(): void
    {
        $suite = new TestSuite(NoTestCases::class);

        $suite->run($this->result);

        $this->assertNotTrue($this->result->wasSuccessful());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertEquals(1, $this->result->warningCount());
        $this->assertCount(1, $this->result);
    }

    public function testNotPublicTestCase(): void
    {
        $suite = new TestSuite(NotPublicTestCase::class);

        $this->assertCount(1, $suite);
    }

    public function testNotVoidTestCase(): void
    {
        $suite = new TestSuite(NotVoidTestCase::class);

        $this->assertCount(1, $suite);
    }

    public function testOneTestCase(): void
    {
        $suite = new TestSuite(OneTestCase::class);

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(0, $this->result->failureCount());
        $this->assertCount(1, $this->result);
        $this->assertTrue($this->result->wasSuccessful());
    }

    public function testShadowedTests(): void
    {
        $suite = new TestSuite(OverrideTestCase::class);

        $suite->run($this->result);

        $this->assertCount(1, $this->result);
    }

    public function testBeforeClassAndAfterClassAnnotations(): void
    {
        $suite = new TestSuite(BeforeClassAndAfterClassTest::class);

        BeforeClassAndAfterClassTest::resetProperties();
        $suite->run($this->result);

        $this->assertEquals(1, BeforeClassAndAfterClassTest::$beforeClassWasRun, '@beforeClass method was not run once for the whole suite.');
        $this->assertEquals(1, BeforeClassAndAfterClassTest::$afterClassWasRun, '@afterClass method was not run once for the whole suite.');
    }

    public function testBeforeClassWithDataProviders(): void
    {
        $suite = new TestSuite(BeforeClassWithOnlyDataProviderTest::class);

        BeforeClassWithOnlyDataProviderTest::resetProperties();
        $suite->run($this->result);

        $this->assertTrue(BeforeClassWithOnlyDataProviderTest::$setUpBeforeClassWasCalled, 'setUpBeforeClass method was not run.');
        $this->assertTrue(BeforeClassWithOnlyDataProviderTest::$beforeClassWasCalled, '@beforeClass method was not run.');
    }

    public function testBeforeAndAfterAnnotations(): void
    {
        $test = new TestSuite(BeforeAndAfterTest::class);

        BeforeAndAfterTest::resetProperties();
        $test->run();

        $this->assertEquals(2, BeforeAndAfterTest::$beforeWasRun);
        $this->assertEquals(2, BeforeAndAfterTest::$afterWasRun);
    }

    public function testPreConditionAndPostConditionAnnotations(): void
    {
        $test = new TestSuite(PreConditionAndPostConditionTest::class);

        PreConditionAndPostConditionTest::resetProperties();
        $test->run();

        $this->assertSame(1, PreConditionAndPostConditionTest::$preConditionWasVerified);
        $this->assertSame(1, PreConditionAndPostConditionTest::$postConditionWasVerified);
    }

    public function testTestWithAnnotation(): void
    {
        $test = new TestSuite(TestWithTest::class);

        BeforeAndAfterTest::resetProperties();
        $result = $test->run();

        $this->assertCount(4, $result->passed());
    }

    public function testSkippedTestDataProvider(): void
    {
        $suite = new TestSuite(DataProviderSkippedTest::class);

        $suite->run($this->result);

        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testItErrorsOnlyOnceOnHookException(): void
    {
        $suite = new TestSuite(TestCaseWithExceptionInHook::class);

        $suite->run($this->result);

        $this->assertEquals(2, $this->result->count());
        $this->assertEquals(1, $this->result->errorCount());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testTestDataProviderDependency(): void
    {
        $suite = new TestSuite(DataProviderDependencyTest::class);

        $suite->run($this->result);

        $skipped           = $this->result->skipped();
        $lastSkippedResult = array_pop($skipped);
        $message           = $lastSkippedResult->thrownException()->getMessage();

        $this->assertStringContainsString('Test for DataProviderDependencyTest::testDependency skipped by data provider', $message);
    }

    public function testIncompleteTestDataProvider(): void
    {
        $suite = new TestSuite(DataProviderIncompleteTest::class);

        $suite->run($this->result);

        $this->assertEquals(3, $this->result->count());
        $this->assertEquals(1, $this->result->notImplementedCount());
    }

    public function testRequirementsBeforeClassHook(): void
    {
        $suite = new TestSuite(RequirementsClassBeforeClassHookTest::class);

        $suite->run($this->result);

        $this->assertEquals(0, $this->result->errorCount());
        $this->assertEquals(1, $this->result->skippedCount());
    }

    public function testDoNotSkipInheritedClass(): void
    {
        $suite = new TestSuite(
            'DontSkipInheritedClass'
        );

        $dir = TEST_FILES_PATH . DIRECTORY_SEPARATOR . 'Inheritance' . DIRECTORY_SEPARATOR;

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
        $suite = new TestSuite(ExceptionInTearDownAfterClassTest::class);
        $suite->run($this->result);

        $this->assertSame(3, $this->result->count());
        $this->assertCount(1, $this->result->failures());

        $failure = $this->result->failures()[0];

        $this->assertSame(
            'Exception in PHPUnit\TestFixture\ExceptionInTearDownAfterClassTest::tearDownAfterClass' . PHP_EOL .
            'throw Exception in tearDownAfterClass()',
            $failure->thrownException()->getMessage()
        );
    }

    public function testNormalizeProvidedDependencies(): void
    {
        $suite = new TestSuite(MultiDependencyTest::class);

        $this->assertEquals([
            MultiDependencyTest::class . '::class',
            MultiDependencyTest::class . '::testOne',
            MultiDependencyTest::class . '::testTwo',
            MultiDependencyTest::class . '::testThree',
            MultiDependencyTest::class . '::testFour',
            MultiDependencyTest::class . '::testFive',
        ], $suite->provides());
    }

    public function testNormalizeRequiredDependencies(): void
    {
        $suite = new TestSuite(MultiDependencyTest::class);

        $this->assertSame([], $suite->requires());
    }

    public function testDetectMissingDependenciesBetweenTestSuites(): void
    {
        $suite = new TestSuite(DependencyOnClassTest::class);

        $this->assertEquals([
            DependencyOnClassTest::class . '::class',
            DependencyOnClassTest::class . '::testThatDependsOnASuccessfulClass',
            DependencyOnClassTest::class . '::testThatDependsOnAFailingClass',
        ], $suite->provides(), 'Provided test names incorrect');

        $this->assertEquals([
            DependencySuccessTest::class . '::class',
            DependencyFailureTest::class . '::class',
        ], $suite->requires(), 'Required test names incorrect');
    }

    public function testResolveDependenciesBetweenTestSuites(): void
    {
        $suite = new TestSuite(DependencyOnClassTest::class);
        $suite->addTestSuite(DependencyFailureTest::class);
        $suite->addTestSuite(DependencySuccessTest::class);

        $this->assertEquals([
            DependencyOnClassTest::class . '::class',
            DependencyOnClassTest::class . '::testThatDependsOnASuccessfulClass',
            DependencyOnClassTest::class . '::testThatDependsOnAFailingClass',
            DependencyFailureTest::class . '::class',
            DependencyFailureTest::class . '::testOne',
            DependencyFailureTest::class . '::testTwo',
            DependencyFailureTest::class . '::testThree',
            DependencyFailureTest::class . '::testFour',
            DependencyFailureTest::class . '::testHandlesDependsAnnotationForNonexistentTests',
            DependencyFailureTest::class . '::testHandlesDependsAnnotationWithNoMethodSpecified',
            DependencySuccessTest::class . '::class',
            DependencySuccessTest::class . '::testOne',
            DependencySuccessTest::class . '::testTwo',
            DependencySuccessTest::class . '::testThree',
        ], $suite->provides(), 'Provided test names incorrect');

        $this->assertEquals([
            DependencyFailureTest::class . '::doesNotExist',
        ], $suite->requires(), 'Required test names incorrect');
    }

    public function testResolverOnlyUsesSuitesAndCases(): void
    {
        $suite = new TestSuite('SomeName');
        $suite->addTest(new DoubleTestCase(new Success));
        $suite->addTestSuite(new TestSuite(DependencyOnClassTest::class));

        $this->assertEquals([
            'SomeName::class',
            DependencyOnClassTest::class . '::class',
            DependencyOnClassTest::class . '::testThatDependsOnASuccessfulClass',
            DependencyOnClassTest::class . '::testThatDependsOnAFailingClass',
        ], $suite->provides(), 'Provided test names incorrect');

        $this->assertEquals([
            DependencySuccessTest::class . '::class',
            DependencyFailureTest::class . '::class',
        ], $suite->requires(), 'Required test names incorrect');
    }
}
