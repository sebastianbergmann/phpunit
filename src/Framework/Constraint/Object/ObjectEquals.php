<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use function get_class;
use function is_object;
use ReflectionNamedType;
use ReflectionObject;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ObjectEquals extends Constraint
{
    private const ACTUAL_IS_NOT_AN_OBJECT = 1;

    private const ACTUAL_DOES_NOT_HAVE_METHOD = 2;

    private const METHOD_DOES_NOT_HAVE_BOOL_RETURN_TYPE = 3;

    private const METHOD_DOES_NOT_ACCEPT_EXACTLY_ONE_ARGUMENT = 4;

    private const PARAMETER_DOES_NOT_HAVE_DECLARED_TYPE = 5;

    private const EXPECTED_NOT_COMPATIBLE_WITH_PARAMETER_TYPE = 6;

    private const OBJECTS_ARE_NOT_EQUAL_ACCORDING_TO_METHOD = 7;

    /**
     * @var object
     */
    private $expected;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $failureReason;

    public function __construct(object $object, string $method = 'equals')
    {
        $this->expected = $object;
        $this->method   = $method;
    }

    public function toString(): string
    {
        return 'two objects are equal';
    }

    protected function matches($other): bool
    {
        if (!is_object($other)) {
            $this->failureReason = self::ACTUAL_IS_NOT_AN_OBJECT;

            return false;
        }

        $object = new ReflectionObject($other);

        if (!$object->hasMethod($this->method)) {
            $this->failureReason = self::ACTUAL_DOES_NOT_HAVE_METHOD;

            return false;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $method = $object->getMethod($this->method);

        if (!$method->hasReturnType()) {
            $this->failureReason = self::METHOD_DOES_NOT_HAVE_BOOL_RETURN_TYPE;

            return false;
        }

        $returnType = $method->getReturnType();

        if (!$returnType instanceof ReflectionNamedType) {
            $this->failureReason = self::METHOD_DOES_NOT_HAVE_BOOL_RETURN_TYPE;

            return false;
        }

        if ($returnType->allowsNull()) {
            $this->failureReason = self::METHOD_DOES_NOT_HAVE_BOOL_RETURN_TYPE;

            return false;
        }

        if ($returnType->getName() !== 'bool') {
            $this->failureReason = self::METHOD_DOES_NOT_HAVE_BOOL_RETURN_TYPE;

            return false;
        }

        if ($method->getNumberOfParameters() !== 1 || $method->getNumberOfRequiredParameters() !== 1) {
            $this->failureReason = self::METHOD_DOES_NOT_ACCEPT_EXACTLY_ONE_ARGUMENT;

            return false;
        }

        $parameter = $method->getParameters()[0];

        if (!$parameter->hasType()) {
            $this->failureReason = self::PARAMETER_DOES_NOT_HAVE_DECLARED_TYPE;

            return false;
        }

        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType) {
            $this->failureReason = self::PARAMETER_DOES_NOT_HAVE_DECLARED_TYPE;

            return false;
        }

        $typeName = $type->getName();

        if ($typeName === 'self') {
            $typeName = get_class($other);
        }

        if (!$this->expected instanceof $typeName) {
            $this->failureReason = self::EXPECTED_NOT_COMPATIBLE_WITH_PARAMETER_TYPE;

            return false;
        }

        if ($other->{$this->method}($this->expected)) {
            return true;
        }

        $this->failureReason = self::OBJECTS_ARE_NOT_EQUAL_ACCORDING_TO_METHOD;

        return false;
    }

    protected function failureDescription($other): string
    {
        return $this->toString();
    }

    protected function additionalFailureDescription($other): string
    {
        switch ($this->failureReason) {
            case self::ACTUAL_IS_NOT_AN_OBJECT:
                return 'Actual value is not an object.';

            case self::ACTUAL_DOES_NOT_HAVE_METHOD:
                return sprintf(
                    '%s::%s() does not exist.',
                    get_class($other),
                    $this->method
                );

            case self::METHOD_DOES_NOT_HAVE_BOOL_RETURN_TYPE:
                return sprintf(
                    '%s::%s() does not declare a bool return type.',
                    get_class($other),
                    $this->method
                );

            case self::METHOD_DOES_NOT_ACCEPT_EXACTLY_ONE_ARGUMENT:
                return sprintf(
                    '%s::%s() does not accept exactly one argument.',
                    get_class($other),
                    $this->method
                );

            case self::PARAMETER_DOES_NOT_HAVE_DECLARED_TYPE:
                return sprintf(
                    'Parameter of %s::%s() does not have a declared type.',
                    get_class($other),
                    $this->method
                );

            case self::EXPECTED_NOT_COMPATIBLE_WITH_PARAMETER_TYPE:
                return sprintf(
                    '%s is not accepted an accepted argument type for %s::%s().',
                    get_class($this->expected),
                    get_class($other),
                    $this->method
                );

            default:
                return sprintf(
                    'The objects are not equal according to %s::%s().',
                    get_class($other),
                    $this->method
                );
        }
    }
}
