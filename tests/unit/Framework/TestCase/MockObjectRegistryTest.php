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

use function assert;
use Closure;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Metadata\Attribute\AllowMockObjectsWithoutExpectationsOnClassTest;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\TestWithDifferentNames;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use stdClass;
use TestOutsideOfPhpunitNamespace;

#[CoversClass(MockObjectRegistry::class)]
#[Small]
final class MockObjectRegistryTest extends TestCase
{
    public function testVerifyDoesNothingWhenNoMockObjectIsRegistered(): void
    {
        $test    = new TestWithDifferentNames('testWithName');
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        (new MockObjectRegistry)->verify($test, $emitter);

        $this->assertSame(0, $test->numberOfAssertionsPerformed());
    }

    public function testVerifyingMockObjectWithInvocationCountExpectationCountsAsAssertion(): void
    {
        $test     = new TestWithDifferentNames('testWithName');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;
        $mock     = $this->mockObject();

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $mock
            ->expects($this->once())
            ->method('doSomething')
            ->willReturn(true);

        $registry->register(AnInterface::class, $mock);

        $mock->doSomething();

        $registry->verify($test, $emitter);

        $this->assertSame(1, $test->numberOfAssertionsPerformed());
    }

    public function testVerifyingMockObjectWithUnmetInvocationCountExpectationFails(): void
    {
        $test     = new TestWithDifferentNames('testWithName');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;
        $mock     = $this->mockObject();

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $mock
            ->expects($this->once())
            ->method('doSomething')
            ->willReturn(true);

        $registry->register(AnInterface::class, $mock);

        $this->expectException(ExpectationFailedException::class);

        $registry->verify($test, $emitter);
    }

    public function testNoticeIsTriggeredWhenNoExpectationsWereConfiguredForMockObject(): void
    {
        $test     = new TestOutsideOfPhpunitNamespace('testOne');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;

        $emitter
            ->expects($this->once())
            ->method('testTriggeredPhpunitNotice')
            ->with(
                $test->valueObjectForEvents(),
                'No expectations were configured for the mock object for ' . AnInterface::class . '. ' .
                'Consider refactoring your test code to use a test stub instead. ' .
                'The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.',
            )
            ->seal();

        $registry->register(AnInterface::class, $this->mockObject());

        $registry->verify($test, $emitter);

        $this->assertSame(0, $test->numberOfAssertionsPerformed());
    }

    #[IgnorePhpunitDeprecations]
    public function testNoticeIsNotTriggeredWhenMockObjectHasParametersRule(): void
    {
        $test     = new TestOutsideOfPhpunitNamespace('testOne');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;
        $mock     = $this->mockObject();

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $mock
            ->method('doSomething')
            ->with()
            ->willReturn(true);

        $registry->register(AnInterface::class, $mock);

        $registry->verify($test, $emitter);
    }

    public function testNoticeIsNotTriggeredWhenTestAllowsMockObjectsWithoutExpectations(): void
    {
        $test     = new AllowMockObjectsWithoutExpectationsOnClassTest('testOne');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $registry->register(AnInterface::class, $this->mockObject());

        $registry->verify($test, $emitter);
    }

    public function testNoticeIsNotTriggeredForTestInPhpunitNamespace(): void
    {
        $test     = new TestWithDifferentNames('testWithName');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $registry->register(AnInterface::class, $this->mockObject());

        $registry->verify($test, $emitter);
    }

    public function testUnsealedMockObjectIsConsideredRiskyWhenSealedMockObjectsAreRequired(): void
    {
        $test     = new TestWithDifferentNames('testWithName');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;
        $mock     = $this->mockObject();

        $emitter
            ->expects($this->once())
            ->method('testConsideredRisky')
            ->with(
                $test->valueObjectForEvents(),
                'Mock object for ' . AnInterface::class . ' has not been sealed',
            )
            ->seal();

        $mock
            ->expects($this->once())
            ->method('doSomething')
            ->willReturn(true);

        $registry->register(AnInterface::class, $mock);

        $mock->doSomething();

        $this->withSealedMockObjectsRequired(
            static function () use ($registry, $test, $emitter): void
            {
                $registry->verify($test, $emitter);
            },
        );
    }

    public function testSealedMockObjectIsNotConsideredRiskyWhenSealedMockObjectsAreRequired(): void
    {
        $test     = new TestWithDifferentNames('testWithName');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;
        $mock     = $this->mockObject();

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $mock
            ->expects($this->once())
            ->method('doSomething')
            ->willReturn(true)
            ->seal();

        $registry->register(AnInterface::class, $mock);

        $mock->doSomething();

        $this->withSealedMockObjectsRequired(
            static function () use ($registry, $test, $emitter): void
            {
                $registry->verify($test, $emitter);
            },
        );
    }

    public function testClearedMockObjectsAreNotVerified(): void
    {
        $test     = new TestWithDifferentNames('testWithName');
        $emitter  = $this->createMock(Emitter::class);
        $registry = new MockObjectRegistry;
        $mock     = $this->mockObject();

        $emitter
            ->expects($this->never())
            ->method($this->anything());

        $mock
            ->expects($this->once())
            ->method('doSomething')
            ->willReturn(true);

        $registry->register(AnInterface::class, $mock);
        $registry->clear();

        $registry->verify($test, $emitter);

        $this->assertSame(0, $test->numberOfAssertionsPerformed());
    }

    public function testExpectationFailureRaisedByInvocationCountRuleCountsAsAssertion(): void
    {
        $test = new TestWithDifferentNames('testWithName');
        $mock = $this->mockObject();

        $mock
            ->expects($this->once())
            ->method('doSomething')
            ->willReturn(true);

        $mock->doSomething();

        try {
            $mock->doSomething();
        } catch (ExpectationFailedException $e) {
            (new MockObjectRegistry)->handleExceptionFromInvokedCountRule($test, $e);
        }

        $this->assertSame(1, $test->numberOfAssertionsPerformed());
    }

    public function testOtherExpectationFailuresDoNotCountAsAssertion(): void
    {
        $test = new TestWithDifferentNames('testWithName');

        (new MockObjectRegistry)->handleExceptionFromInvokedCountRule($test, new ExpectationFailedException('message'));

        $this->assertSame(0, $test->numberOfAssertionsPerformed());
    }

    public function testExceptionsThatAreNotExpectationFailuresDoNotCountAsAssertion(): void
    {
        $test = new TestWithDifferentNames('testWithName');

        (new MockObjectRegistry)->handleExceptionFromInvokedCountRule($test, new RuntimeException('message'));

        $this->assertSame(0, $test->numberOfAssertionsPerformed());
    }

    public function testShouldInvocationMockerBeResetReturnsFalseWhenMockIsAmongDependencyInput(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $mock     = $this->createMock(stdClass::class);

        $testCase->setDependencyInput(['previousTest' => $mock]);

        $method = new ReflectionMethod(MockObjectRegistry::class, 'shouldInvocationMockerBeReset');

        $this->assertFalse($method->invoke(new MockObjectRegistry, $testCase, $mock));
    }

    public function testShouldInvocationMockerBeResetReturnsFalseWhenMockIsAmongTestResult(): void
    {
        $testCase = new TestWithDifferentNames('testWithName');
        $mock     = $this->createMock(stdClass::class);

        $testCase->setResult([$mock]);

        $method = new ReflectionMethod(MockObjectRegistry::class, 'shouldInvocationMockerBeReset');

        $this->assertFalse($method->invoke(new MockObjectRegistry, $testCase, $mock));
    }

    /**
     * @param Closure(): void $callback
     */
    private function withSealedMockObjectsRequired(Closure $callback): void
    {
        $property              = new ReflectionProperty(ConfigurationRegistry::class, 'instance');
        $originalConfiguration = $property->getValue();

        $property->setValue(
            null,
            new Merger($this->createStub(Emitter::class))->merge(
                new Builder($this->createStub(Emitter::class))->fromParameters([]),
                new Loader($this->createStub(Emitter::class))->load(__DIR__ . '/_files/require-sealed-mock-objects.xml'),
            ),
        );

        try {
            $callback();
        } finally {
            $property->setValue(null, $originalConfiguration);
        }
    }

    /**
     * Creates a mock object that is not registered with this test so that
     * only the registry under test verifies it.
     *
     * @return AnInterface&MockObject
     */
    private function mockObject(): MockObject
    {
        $mock = (new Generator)->testDouble(
            AnInterface::class,
            true,
            callOriginalConstructor: false,
            callOriginalClone: false,
        );

        assert($mock instanceof AnInterface);
        assert($mock instanceof MockObject);

        return $mock;
    }
}
