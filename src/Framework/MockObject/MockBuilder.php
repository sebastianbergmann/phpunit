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

use function assert;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Generator\ClassIsEnumerationException;
use PHPUnit\Framework\MockObject\Generator\ClassIsFinalException;
use PHPUnit\Framework\MockObject\Generator\DuplicateMethodException;
use PHPUnit\Framework\MockObject\Generator\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\Generator\NameAlreadyInUseException;
use PHPUnit\Framework\MockObject\Generator\ReflectionException;
use PHPUnit\Framework\MockObject\Generator\RuntimeException;
use PHPUnit\Framework\MockObject\Generator\UnknownTypeException;
use PHPUnit\Framework\TestCase;

/**
 * @template MockedType
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class MockBuilder extends TestDoubleBuilder
{
    private readonly TestCase $testCase;

    /**
     * @var ?class-string
     */
    private ?string $mockClassName = null;

    /**
     * @param class-string|trait-string $type
     */
    public function __construct(TestCase $testCase, string $type)
    {
        parent::__construct($type);

        $this->testCase = $testCase;
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
        $object = $this->getTestDouble($this->mockClassName, true);

        assert($object instanceof $this->type);
        assert($object instanceof MockObject);

        $this->testCase->registerMockObject($this->type, $object);

        return $object;
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
}
