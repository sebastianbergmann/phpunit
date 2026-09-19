<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\DependencyResolver\LargeProviderTest;
use PHPUnit\TestFixture\DependencyResolver\ProviderTest;
use PHPUnit\TestFixture\DependencyResolver\SmallConsumerTest;
use PHPUnit\TestFixture\TestWithDifferentNames;
use PHPUnit\TestRunner\TestResult\PassedTests;
use ReflectionProperty;
use stdClass;

#[CoversClass(DependencyResolver::class)]
#[Small]
final class DependencyResolverTest extends TestCase
{
    private ?PassedTests $originalPassedTests;
    private PassedTests $passedTests;

    protected function setUp(): void
    {
        $property = new ReflectionProperty(PassedTests::class, 'instance');

        $this->originalPassedTests = $property->getValue();
        $this->passedTests         = new PassedTests;

        $property->setValue(null, $this->passedTests);
    }

    protected function tearDown(): void
    {
        new ReflectionProperty(PassedTests::class, 'instance')->setValue(null, $this->originalPassedTests);
    }

    public function testTestWithoutDependenciesCanBeRun(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $this->assertTrue((new DependencyResolver)->resolve($test, [], $emitter));
        $this->assertFalse($test->hasDependencyInput());
    }

    public function testTestThatDependsOnClassThatDoesNotExistCannotBeRun(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testErrored')
            ->seal();

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency('PHPUnit\\TestFixture\\DoesNotExist')],
            $emitter,
        );

        $this->assertFalse($result);
        $this->assertTrue($test->status()->isError());
        $this->assertSame('This test depends on "PHPUnit\TestFixture\DoesNotExist" which does not exist', $test->status()->message());
    }

    public function testTestThatDependsOnClassThatDidNotPassCannotBeRun(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testSkipped')
            ->seal();

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class)],
            $emitter,
        );

        $this->assertFalse($result);
        $this->assertTrue($test->status()->isSkipped());
        $this->assertSame('This test depends on "PHPUnit\TestFixture\DependencyResolver\ProviderTest::class" to pass', $test->status()->message());
    }

    public function testTestThatDependsOnClassThatPassedCanBeRun(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $this->passedTests->testClassPassed(ProviderTest::class);

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class)],
            $emitter,
        );

        $this->assertTrue($result);
        $this->assertFalse($test->hasDependencyInput());
    }

    public function testTestThatDependsOnMethodThatDoesNotExistCannotBeRun(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testErrored')
            ->seal();

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class, 'testDoesNotExist')],
            $emitter,
        );

        $this->assertFalse($result);
        $this->assertTrue($test->status()->isError());
        $this->assertSame('This test depends on "PHPUnit\TestFixture\DependencyResolver\ProviderTest::testDoesNotExist" which does not exist', $test->status()->message());
    }

    public function testTestThatDependsOnMethodThatDidNotPassCannotBeRun(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testSkipped')
            ->seal();

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class, 'testReturnsVoid')],
            $emitter,
        );

        $this->assertFalse($result);
        $this->assertTrue($test->status()->isSkipped());
        $this->assertSame('This test depends on "PHPUnit\TestFixture\DependencyResolver\ProviderTest::testReturnsVoid" to pass', $test->status()->message());
    }

    public function testTestThatDependsOnLargerTestIsConsideredRisky(): void
    {
        $test    = new SmallConsumerTest('testOne');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testConsideredRisky')
            ->with($test->valueObjectForEvents(), 'This test depends on a test that is larger than itself')
            ->seal();

        $this->markAsPassed(new LargeProviderTest('testOne'), null);

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(LargeProviderTest::class, 'testOne')],
            $emitter,
        );

        $this->assertTrue($result);
        $this->assertFalse($test->hasDependencyInput());
    }

    public function testTestThatDependsOnMethodWithoutReturnValueCanBeRun(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $this->markAsPassed(new ProviderTest('testReturnsVoid'), null);

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class, 'testReturnsVoid')],
            $emitter,
        );

        $this->assertTrue($result);
        $this->assertFalse($test->hasDependencyInput());
    }

    public function testReturnValueOfDependencyIsPassedToTest(): void
    {
        $test        = new TestWithDifferentNames('testWithName');
        $emitter     = $this->createMock(Emitter::class);
        $returnValue = new stdClass;

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $this->markAsPassed(new ProviderTest('testReturnsObject'), $returnValue);

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class, 'testReturnsObject')],
            $emitter,
        );

        $this->assertTrue($result);
        $this->assertTrue($test->hasDependencyInput());
        $this->assertSame(
            [ProviderTest::class . '::testReturnsObject' => $returnValue],
            $test->dependencyInput(),
        );
    }

    public function testReturnValueOfDependencyIsShallowClonedWhenRequested(): void
    {
        $test               = new TestWithDifferentNames('testWithName');
        $emitter            = $this->createMock(Emitter::class);
        $returnValue        = new stdClass;
        $returnValue->child = new stdClass;

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $this->markAsPassed(new ProviderTest('testReturnsObject'), $returnValue);

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class, 'testReturnsObject', shallowClone: true)],
            $emitter,
        );

        $this->assertTrue($result);

        $input = $test->dependencyInput()[ProviderTest::class . '::testReturnsObject'];

        $this->assertInstanceOf(stdClass::class, $input);
        $this->assertNotSame($returnValue, $input);
        $this->assertSame($returnValue->child, $input->child);
    }

    public function testReturnValueOfDependencyIsDeepClonedWhenRequested(): void
    {
        $test               = new TestWithDifferentNames('testWithName');
        $emitter            = $this->createMock(Emitter::class);
        $returnValue        = new stdClass;
        $returnValue->child = new stdClass;

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $this->markAsPassed(new ProviderTest('testReturnsObject'), $returnValue);

        $result = (new DependencyResolver)->resolve(
            $test,
            [new ExecutionOrderDependency(ProviderTest::class, 'testReturnsObject', deepClone: true)],
            $emitter,
        );

        $this->assertTrue($result);

        $input = $test->dependencyInput()[ProviderTest::class . '::testReturnsObject'];

        $this->assertInstanceOf(stdClass::class, $input);
        $this->assertNotSame($returnValue, $input);
        $this->assertNotSame($returnValue->child, $input->child);
        $this->assertEquals($returnValue, $input);
    }

    private function markAsPassed(TestCase $test, mixed $returnValue): void
    {
        $this->passedTests->testMethodPassed(
            TestMethodBuilder::fromTestCase($test),
            $returnValue,
        );
    }
}
