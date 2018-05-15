<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * Implementation of the Builder pattern for Mock objects.
 */
class MockBuilder
{
    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var array
     */
    private $methodsExcept = [];

    /**
     * @var string
     */
    private $mockClassName = '';

    /**
     * @var array
     */
    private $constructorArgs = [];

    /**
     * @var bool
     */
    private $originalConstructor = true;

    /**
     * @var bool
     */
    private $originalClone = true;

    /**
     * @var bool
     */
    private $autoload = true;

    /**
     * @var bool
     */
    private $cloneArguments = false;

    /**
     * @var bool
     */
    private $callOriginalMethods = false;

    /**
     * @var object
     */
    private $proxyTarget;

    /**
     * @var bool
     */
    private $allowMockingUnknownTypes = true;

    /**
     * @var bool
     */
    private $returnValueGeneration = true;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @param TestCase     $testCase
     * @param array|string $type
     */
    public function __construct(TestCase $testCase, $type)
    {
        $this->testCase  = $testCase;
        $this->type      = $type;
        $this->generator = new Generator;
    }

    /**
     * Creates a mock object using a fluent interface.
     */
    public function getMock(): MockObject
    {
        $object = $this->generator->getMock(
            $this->type,
            $this->methods,
            $this->constructorArgs,
            $this->mockClassName,
            $this->originalConstructor,
            $this->originalClone,
            $this->autoload,
            $this->cloneArguments,
            $this->callOriginalMethods,
            $this->proxyTarget,
            $this->allowMockingUnknownTypes,
            $this->returnValueGeneration
        );

        $this->testCase->registerMockObject($object);

        return $object;
    }

    /**
     * Creates a mock object for an abstract class using a fluent interface.
     */
    public function getMockForAbstractClass(): MockObject
    {
        $object = $this->generator->getMockForAbstractClass(
            $this->type,
            $this->constructorArgs,
            $this->mockClassName,
            $this->originalConstructor,
            $this->originalClone,
            $this->autoload,
            $this->methods,
            $this->cloneArguments
        );

        $this->testCase->registerMockObject($object);

        return $object;
    }

    /**
     * Creates a mock object for a trait using a fluent interface.
     */
    public function getMockForTrait(): MockObject
    {
        $object = $this->generator->getMockForTrait(
            $this->type,
            $this->constructorArgs,
            $this->mockClassName,
            $this->originalConstructor,
            $this->originalClone,
            $this->autoload,
            $this->methods,
            $this->cloneArguments
        );

        $this->testCase->registerMockObject($object);

        return $object;
    }

    /**
     * Specifies the subset of methods to mock. Default is to mock none of them.
     *
     * @param null|array $methods
     */
    public function setMethods(array $methods = null): MockBuilder
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Specifies the subset of methods to not mock. Default is to mock all of them.
     *
     * @param array $methods
     */
    public function setMethodsExcept(array $methods = []): MockBuilder
    {
        $this->methodsExcept = $methods;

        $this->setMethods(
            \array_diff(
                $this->generator->getClassMethods($this->type),
                $this->methodsExcept
            )
        );

        return $this;
    }

    /**
     * Specifies the arguments for the constructor.
     *
     * @param array $args
     */
    public function setConstructorArgs(array $args): MockBuilder
    {
        $this->constructorArgs = $args;

        return $this;
    }

    /**
     * Specifies the name for the mock class.
     */
    public function setMockClassName(string $name): MockBuilder
    {
        $this->mockClassName = $name;

        return $this;
    }

    /**
     * Disables the invocation of the original constructor.
     */
    public function disableOriginalConstructor(): MockBuilder
    {
        $this->originalConstructor = false;

        return $this;
    }

    /**
     * Enables the invocation of the original constructor.
     */
    public function enableOriginalConstructor(): MockBuilder
    {
        $this->originalConstructor = true;

        return $this;
    }

    /**
     * Disables the invocation of the original clone constructor.
     */
    public function disableOriginalClone(): MockBuilder
    {
        $this->originalClone = false;

        return $this;
    }

    /**
     * Enables the invocation of the original clone constructor.
     */
    public function enableOriginalClone(): MockBuilder
    {
        $this->originalClone = true;

        return $this;
    }

    /**
     * Disables the use of class autoloading while creating the mock object.
     */
    public function disableAutoload(): MockBuilder
    {
        $this->autoload = false;

        return $this;
    }

    /**
     * Enables the use of class autoloading while creating the mock object.
     */
    public function enableAutoload(): MockBuilder
    {
        $this->autoload = true;

        return $this;
    }

    /**
     * Disables the cloning of arguments passed to mocked methods.
     */
    public function disableArgumentCloning(): MockBuilder
    {
        $this->cloneArguments = false;

        return $this;
    }

    /**
     * Enables the cloning of arguments passed to mocked methods.
     */
    public function enableArgumentCloning(): MockBuilder
    {
        $this->cloneArguments = true;

        return $this;
    }

    /**
     * Enables the invocation of the original methods.
     */
    public function enableProxyingToOriginalMethods(): MockBuilder
    {
        $this->callOriginalMethods = true;

        return $this;
    }

    /**
     * Disables the invocation of the original methods.
     */
    public function disableProxyingToOriginalMethods(): MockBuilder
    {
        $this->callOriginalMethods = false;
        $this->proxyTarget         = null;

        return $this;
    }

    /**
     * Sets the proxy target.
     *
     * @param object $object
     */
    public function setProxyTarget($object): MockBuilder
    {
        $this->proxyTarget = $object;

        return $this;
    }

    public function allowMockingUnknownTypes(): MockBuilder
    {
        $this->allowMockingUnknownTypes = true;

        return $this;
    }

    public function disallowMockingUnknownTypes(): MockBuilder
    {
        $this->allowMockingUnknownTypes = false;

        return $this;
    }

    public function enableAutoReturnValueGeneration(): MockBuilder
    {
        $this->returnValueGeneration = true;

        return $this;
    }

    public function disableAutoReturnValueGeneration(): MockBuilder
    {
        $this->returnValueGeneration = false;

        return $this;
    }
}
