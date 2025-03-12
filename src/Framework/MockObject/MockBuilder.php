<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function array_merge;
use function assert;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Generator\ClassIsEnumerationException;
use PHPUnit\Framework\MockObject\Generator\ClassIsFinalException;
use PHPUnit\Framework\MockObject\Generator\DuplicateMethodException;
use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\Generator\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\Generator\NameAlreadyInUseException;
use PHPUnit\Framework\MockObject\Generator\ReflectionException;
use PHPUnit\Framework\MockObject\Generator\RuntimeException;
use PHPUnit\Framework\MockObject\Generator\UnknownTypeException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @template MockedType
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class MockBuilder
{
    private readonly TestCase $testCase;

    /**
     * @var class-string|trait-string
     */
    private readonly string $type;

    /**
     * @var list<non-empty-string>
     */
    private array $methods          = [];
    private bool $emptyMethodsArray = false;

    /**
     * @var ?class-string
     */
    private ?string $mockClassName = null;

    /**
     * @var array<mixed>
     */
    private array $constructorArgs      = [];
    private bool $originalConstructor   = true;
    private bool $originalClone         = true;
    private bool $returnValueGeneration = true;
    private readonly Generator $generator;

    /**
     * @param class-string|trait-string $type
     */
    public function __construct(TestCase $testCase, string $type)
    {
        $this->testCase  = $testCase;
        $this->type      = $type;
        $this->generator = new Generator;
    }

    /**
     * Creates a mock object using a fluent interface.
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws InvalidArgumentException
     * @throws InvalidMethodNameException
     * @throws NameAlreadyInUseException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     *
     * @return MockedType&MockObject
     */
    public function getMock(): MockObject
    {
        $object = $this->generator->testDouble(
            $this->type,
            true,
            !$this->emptyMethodsArray ? $this->methods : null,
            $this->constructorArgs,
            $this->mockClassName ?? '',
            $this->originalConstructor,
            $this->originalClone,
            $this->returnValueGeneration,
        );

        assert($object instanceof $this->type);
        assert($object instanceof MockObject);

        $this->testCase->registerMockObject($object);

        return $object;
    }

    /**
     * Specifies the subset of methods to mock, requiring each to exist in the class.
     *
     * @param list<non-empty-string> $methods
     *
     * @throws CannotUseOnlyMethodsException
     * @throws ReflectionException
     *
     * @return $this
     */
    public function onlyMethods(array $methods): self
    {
        if ($methods === []) {
            $this->emptyMethodsArray = true;

            return $this;
        }

        try {
            $reflector = new ReflectionClass($this->type);

            // @codeCoverageIgnoreStart
            /** @phpstan-ignore catch.neverThrown */
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
            // @codeCoverageIgnoreEnd
        }

        foreach ($methods as $method) {
            if (!$reflector->hasMethod($method)) {
                throw new CannotUseOnlyMethodsException($this->type, $method);
            }
        }

        $this->methods = array_merge($this->methods, $methods);

        return $this;
    }

    /**
     * Specifies the arguments for the constructor.
     *
     * @param array<mixed> $arguments
     *
     * @return $this
     */
    public function setConstructorArgs(array $arguments): self
    {
        $this->constructorArgs = $arguments;

        return $this;
    }

    /**
     * Specifies the name for the mock class.
     *
     * @param class-string $name
     *
     * @return $this
     */
    public function setMockClassName(string $name): self
    {
        $this->mockClassName = $name;

        return $this;
    }

    /**
     * Disables the invocation of the original constructor.
     *
     * @return $this
     */
    public function disableOriginalConstructor(): self
    {
        $this->originalConstructor = false;

        return $this;
    }

    /**
     * Enables the invocation of the original constructor.
     *
     * @return $this
     */
    public function enableOriginalConstructor(): self
    {
        $this->originalConstructor = true;

        return $this;
    }

    /**
     * Disables the invocation of the original clone constructor.
     *
     * @return $this
     */
    public function disableOriginalClone(): self
    {
        $this->originalClone = false;

        return $this;
    }

    /**
     * Enables the invocation of the original clone constructor.
     *
     * @return $this
     */
    public function enableOriginalClone(): self
    {
        $this->originalClone = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableAutoReturnValueGeneration(): self
    {
        $this->returnValueGeneration = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableAutoReturnValueGeneration(): self
    {
        $this->returnValueGeneration = false;

        return $this;
    }
}
