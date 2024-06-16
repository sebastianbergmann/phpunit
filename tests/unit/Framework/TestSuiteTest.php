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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\AbstractTestCase;
use PHPUnit\TestFixture\DependencyFailureTest;
use PHPUnit\TestFixture\DependencyOnClassTest;
use PHPUnit\TestFixture\DependencySuccessTest;
use PHPUnit\TestFixture\MultiDependencyTest;
use PHPUnit\TestFixture\NoTestCase;
use PHPUnit\TestFixture\NotPublicTestCase;
use ReflectionClass;

#[CoversClass(TestSuite::class)]
#[Small]
final class TestSuiteTest extends TestCase
{
    public function testNotPublicTestCase(): void
    {
        $suite = TestSuite::fromClassReflector(new ReflectionClass(NotPublicTestCase::class));

        $this->assertCount(1, $suite);
    }

    public function testNormalizeProvidedDependencies(): void
    {
        $suite = TestSuite::fromClassReflector(new ReflectionClass(MultiDependencyTest::class));

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
        $suite = TestSuite::fromClassReflector(new ReflectionClass(MultiDependencyTest::class));

        $this->assertSame([], $suite->requires());
    }

    public function testDetectMissingDependenciesBetweenTestSuites(): void
    {
        $suite = TestSuite::fromClassReflector(
            new ReflectionClass(DependencyOnClassTest::class),
        );

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
        $suite = TestSuite::fromClassReflector(new ReflectionClass(DependencyOnClassTest::class));
        $suite->addTestSuite(new ReflectionClass(DependencyFailureTest::class));
        $suite->addTestSuite(new ReflectionClass(DependencySuccessTest::class));

        $this->assertEquals([
            DependencyOnClassTest::class . '::class',
            DependencyOnClassTest::class . '::testThatDependsOnASuccessfulClass',
            DependencyOnClassTest::class . '::testThatDependsOnAFailingClass',
            DependencyFailureTest::class . '::class',
            DependencyFailureTest::class . '::testOne',
            DependencyFailureTest::class . '::testTwo',
            DependencyFailureTest::class . '::testThree',
            DependencyFailureTest::class . '::testFour',
            DependencyFailureTest::class . '::testHandlesDependencyOnTestMethodThatDoesNotExist',
            DependencyFailureTest::class . '::testHandlesDependencyOnTestMethodWithEmptyName',
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
        $suite = TestSuite::empty('SomeName');
        $suite->addTestSuite(new ReflectionClass(DependencyOnClassTest::class));

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

    public function testRejectsAbstractTestClass(): void
    {
        $suite = TestSuite::empty('the-test-suite');

        $this->expectException(Exception::class);

        $suite->addTestSuite(new ReflectionClass(AbstractTestCase::class));
    }

    public function testRejectsClassThatDoesNotExtendTestClass(): void
    {
        $suite = TestSuite::empty('the-test-suite');

        $this->expectException(Exception::class);

        $suite->addTestSuite(new ReflectionClass(NoTestCase::class));
    }
}
