<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function assert;
use function lcfirst;
use function sprintf;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

/**
 * Thrown when no generator can be derived from a type. The message names the
 * parameter and the type that could not be handled.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CannotDeriveGeneratorException extends RuntimeException implements Exception
{
    public static function unsupportedType(string $type): self
    {
        return new self(sprintf('%s is not supported', $type));
    }

    public static function unknownType(string $name): self
    {
        return new self(sprintf('%s is neither a supported type nor the name of an existing class', $name));
    }

    public static function interfaceType(string $name): self
    {
        return new self(sprintf('%s is an interface', $name));
    }

    public static function traitType(string $name): self
    {
        return new self(sprintf('%s is a trait', $name));
    }

    public static function abstractClass(string $name): self
    {
        return new self(sprintf('class %s is abstract', $name));
    }

    public static function noPublicConstructor(string $name): self
    {
        return new self(sprintf('class %s does not have a public constructor', $name));
    }

    public static function enumerationWithoutCases(string $name): self
    {
        return new self(sprintf('enumeration %s has no cases', $name));
    }

    public static function intersectionType(string $type): self
    {
        return new self(sprintf('intersection type %s is not supported', $type));
    }

    public static function invalidTypeArguments(string $type, string $expected): self
    {
        return new self(sprintf('%s is not supported, %s', $type, $expected));
    }

    public static function invalidIntegerRange(string $type): self
    {
        return new self(sprintf('the minimum of %s is greater than its maximum', $type));
    }

    public static function forParameter(ReflectionParameter $parameter, string $type, self $reason): self
    {
        return new self(
            sprintf(
                'Cannot derive a generator for parameter $%s of type %s: %s',
                $parameter->getName(),
                $type,
                $reason->getMessage(),
            ),
            0,
            $reason,
        );
    }

    public static function forConstructorParameter(ReflectionParameter $parameter, string $type, self $reason): self
    {
        $constructor = $parameter->getDeclaringFunction();

        assert($constructor instanceof ReflectionMethod);

        return new self(
            sprintf(
                'cannot derive a generator for parameter $%s of %s::%s() of type %s: %s',
                $parameter->getName(),
                $constructor->getDeclaringClass()->getName(),
                $constructor->getName(),
                $type,
                $reason->getMessage(),
            ),
            0,
            $reason,
        );
    }

    public static function unparsableConstructorParameterType(ReflectionParameter $parameter, InvalidTypeException $reason): self
    {
        $constructor = $parameter->getDeclaringFunction();

        assert($constructor instanceof ReflectionMethod);

        return new self(
            sprintf(
                'cannot derive a generator for parameter $%s of %s::%s(), the type in its @param tag cannot be parsed: %s',
                $parameter->getName(),
                $constructor->getDeclaringClass()->getName(),
                $constructor->getName(),
                lcfirst($reason->getMessage()),
            ),
            0,
            $reason,
        );
    }

    public static function unparsableParameterType(ReflectionParameter $parameter, InvalidTypeException $reason): self
    {
        return new self(
            sprintf(
                'Cannot derive a generator for parameter $%s, the type in its @param tag cannot be parsed: %s',
                $parameter->getName(),
                lcfirst($reason->getMessage()),
            ),
            0,
            $reason,
        );
    }

    public static function variadicParameter(ReflectionParameter $parameter): self
    {
        return new self(
            sprintf(
                'Cannot derive a generator for parameter $%s, variadic parameters are not supported',
                $parameter->getName(),
            ),
        );
    }
}
