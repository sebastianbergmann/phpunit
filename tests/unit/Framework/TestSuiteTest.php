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

use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\BeforeAndAfterTest;
use PHPUnit\TestFixture\DependencyFailureTest;
use PHPUnit\TestFixture\DependencyOnClassTest;
use PHPUnit\TestFixture\DependencySuccessTest;
use PHPUnit\TestFixture\DoubleTestCase;
use PHPUnit\TestFixture\MultiDependencyTest;
use PHPUnit\TestFixture\NotPublicTestCase;
use PHPUnit\TestFixture\NotVoidTestCase;
use PHPUnit\TestFixture\PreConditionAndPostConditionTest;
use PHPUnit\TestFixture\Success;

#[CoversClass(TestSuite::class)]
#[Small]
final class TestSuiteTest extends TestCase
{
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

    public function testBeforeAndAfterAnnotations(): void
    {
        $test = new TestSuite(BeforeAndAfterTest::class);

        BeforeAndAfterTest::resetProperties();

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertEquals(2, BeforeAndAfterTest::$beforeWasRun);
        $this->assertEquals(2, BeforeAndAfterTest::$afterWasRun);
    }

    public function testPreConditionAndPostConditionAnnotations(): void
    {
        $test = new TestSuite(PreConditionAndPostConditionTest::class);

        PreConditionAndPostConditionTest::resetProperties();

        Facade::suspend();
        $test->run(new TestResult);
        Facade::resume();

        $this->assertSame(1, PreConditionAndPostConditionTest::$preConditionWasVerified);
        $this->assertSame(1, PreConditionAndPostConditionTest::$postConditionWasVerified);
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
        $suite->addTest(new DoubleTestCase(new Success('testOne')));
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
