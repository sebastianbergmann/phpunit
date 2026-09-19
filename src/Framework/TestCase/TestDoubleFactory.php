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
use function implode;
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\MockObject\Generator\Generator as MockGenerator;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;

/**
 * Creates the test doubles that a test case class requests, registers mock
 * objects with the test that requested them, and emits the corresponding
 * events.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestDoubleFactory
{
    /**
     * @var class-string
     */
    private string $testClassName;
    private Event\Emitter $emitter;

    /**
     * @param class-string $testClassName
     */
    public function __construct(string $testClassName, Event\Emitter $emitter)
    {
        $this->testClassName = $testClassName;
        $this->emitter       = $emitter;
    }

    /**
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return MockObject&RealInstanceType
     */
    public function createMock(TestCase $test, string $type): MockObject
    {
        $mock = (new MockGenerator)->testDouble(
            $type,
            true,
            callOriginalConstructor: false,
            callOriginalClone: false,
            returnValueGeneration: $this->generateReturnValues(),
        );

        assert($mock instanceof $type);
        assert($mock instanceof MockObject);

        $test->registerMockObject($type, $mock);

        $this->emitter->testCreatedMockObject($type);

        return $mock;
    }

    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    public function createMockForIntersectionOfInterfaces(TestCase $test, array $interfaces): MockObject
    {
        $mock = (new MockGenerator)->testDoubleForInterfaceIntersection(
            $interfaces,
            true,
            returnValueGeneration: $this->generateReturnValues(),
        );

        assert($mock instanceof MockObject);

        $type = implode('|', $interfaces);

        assert($type !== '');

        $test->registerMockObject($type, $mock);

        $this->emitter->testCreatedMockObjectForIntersectionOfInterfaces($interfaces);

        return $mock;
    }

    /**
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     * @param array<non-empty-string, mixed> $configuration
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return MockObject&RealInstanceType
     */
    public function createConfiguredMock(TestCase $test, string $type, array $configuration): MockObject
    {
        $mock = $this->createMock($test, $type);

        foreach ($configuration as $method => $return) {
            $mock->method($method)->willReturn($return);
        }

        return $mock;
    }

    /**
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     * @param list<non-empty-string>         $methods
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     *
     * @return MockObject&RealInstanceType
     */
    public function createPartialMock(TestCase $test, string $type, array $methods): MockObject
    {
        $mockBuilder = new MockBuilder($test, $type)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods($methods);

        if (!$this->generateReturnValues()) {
            $mockBuilder->disableAutoReturnValueGeneration();
        }

        $partialMock = $mockBuilder->getMock();

        $this->emitter->testCreatedPartialMockObject(
            $type,
            ...$methods,
        );

        return $partialMock;
    }

    /**
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return RealInstanceType&Stub
     */
    public function createStub(string $type): Stub
    {
        $stub = (new MockGenerator)->testDouble(
            $type,
            false,
            callOriginalConstructor: false,
            callOriginalClone: false,
            returnValueGeneration: $this->generateReturnValues(),
        );

        $this->emitter->testCreatedStub($type);

        assert($stub instanceof $type);
        assert($stub instanceof Stub);

        return $stub;
    }

    /**
     * @param list<class-string> $interfaces
     *
     * @throws MockObjectException
     */
    public function createStubForIntersectionOfInterfaces(array $interfaces): Stub
    {
        $stub = (new MockGenerator)->testDoubleForInterfaceIntersection(
            $interfaces,
            false,
            returnValueGeneration: $this->generateReturnValues(),
        );

        $this->emitter->testCreatedStubForIntersectionOfInterfaces($interfaces);

        return $stub;
    }

    /**
     * @template RealInstanceType of object
     *
     * @param class-string<RealInstanceType> $type
     * @param array<non-empty-string, mixed> $configuration
     *
     * @throws InvalidArgumentException
     * @throws MockObjectException
     * @throws NoPreviousThrowableException
     *
     * @return RealInstanceType&Stub
     */
    public function createConfiguredStub(string $type, array $configuration): Stub
    {
        $stub = $this->createStub($type);

        foreach ($configuration as $method => $return) {
            $stub->method($method)->willReturn($return);
        }

        return $stub;
    }

    private function generateReturnValues(): bool
    {
        return MetadataRegistry::parser()->forClass($this->testClassName)->isDisableReturnValueGenerationForTestDoubles()->isEmpty();
    }
}
