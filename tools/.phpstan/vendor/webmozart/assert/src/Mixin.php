<?php

declare(strict_types=1);

namespace Webmozart\Assert;

use ArrayAccess;
use Closure;
use Countable;
use Throwable;

/**
 * This trait provides nullOr*, all* and allNullOr* variants of assertion base methods.
 * Do not use this trait directly: it will change, and is not designed for reuse.
 */
trait Mixin
{
    /**
     * @psalm-pure
     *
     * @psalm-assert string|null $value
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrString(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::string($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allString(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::string($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrString(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::string($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert non-empty-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return non-empty-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrStringNotEmpty(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::stringNotEmpty($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allStringNotEmpty(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::stringNotEmpty($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrStringNotEmpty(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::stringNotEmpty($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert int|null $value
     *
     * @param string|callable():string $message
     *
     * @return int|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrInteger(mixed $value, callable|string $message = ''): ?int
    {
        null === $value || static::integer($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<int>
     *
     * @throws InvalidArgumentException
     */
    public static function allInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::integer($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<int|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::integer($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert numeric|null $value
     *
     * @param string|callable():string $message
     *
     * @return numeric|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIntegerish(mixed $value, callable|string $message = ''): string|int|float|null
    {
        null === $value || static::integerish($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric>
     *
     * @throws InvalidArgumentException
     */
    public static function allIntegerish(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::integerish($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIntegerish(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::integerish($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert positive-int|null $value
     *
     * @param string|callable():string $message
     *
     * @return positive-int|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrPositiveInteger(mixed $value, callable|string $message = ''): ?int
    {
        null === $value || static::positiveInteger($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int>
     *
     * @throws InvalidArgumentException
     */
    public static function allPositiveInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::positiveInteger($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrPositiveInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::positiveInteger($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert non-negative-int|null $value
     *
     * @param string|callable():string $message
     *
     * @return non-negative-int|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotNegativeInteger(mixed $value, callable|string $message = ''): ?int
    {
        null === $value || static::notNegativeInteger($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-negative-int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-negative-int>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotNegativeInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notNegativeInteger($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-negative-int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-negative-int|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotNegativeInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notNegativeInteger($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert negative-int|null $value
     *
     * @param string|callable():string $message
     *
     * @return negative-int|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNegativeInteger(mixed $value, callable|string $message = ''): ?int
    {
        null === $value || static::negativeInteger($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<negative-int> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<negative-int>
     *
     * @throws InvalidArgumentException
     */
    public static function allNegativeInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::negativeInteger($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<negative-int|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<negative-int|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNegativeInteger(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::negativeInteger($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert float|null $value
     *
     * @param string|callable():string $message
     *
     * @return float|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrFloat(mixed $value, callable|string $message = ''): ?float
    {
        null === $value || static::float($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<float> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<float>
     *
     * @throws InvalidArgumentException
     */
    public static function allFloat(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::float($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<float|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<float|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrFloat(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::float($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert numeric|null $value
     *
     * @param string|callable():string $message
     *
     * @return numeric|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNumeric(mixed $value, callable|string $message = ''): string|int|float|null
    {
        null === $value || static::numeric($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric>
     *
     * @throws InvalidArgumentException
     */
    public static function allNumeric(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::numeric($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<numeric|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<numeric|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNumeric(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::numeric($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert positive-int|0|null $value
     *
     * @param string|callable():string $message
     *
     * @return positive-int|0|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNatural(mixed $value, callable|string $message = ''): ?int
    {
        null === $value || static::natural($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int|0> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int|0>
     *
     * @throws InvalidArgumentException
     */
    public static function allNatural(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::natural($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<positive-int|0|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<positive-int|0|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNatural(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::natural($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert bool|null $value
     *
     * @param string|callable():string $message
     *
     * @return bool|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrBoolean(mixed $value, callable|string $message = ''): ?bool
    {
        null === $value || static::boolean($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<bool> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<bool>
     *
     * @throws InvalidArgumentException
     */
    public static function allBoolean(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::boolean($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<bool|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<bool|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrBoolean(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::boolean($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert scalar|null $value
     *
     * @param string|callable():string $message
     *
     * @return scalar|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrScalar(mixed $value, callable|string $message = ''): string|int|float|bool|null
    {
        null === $value || static::scalar($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<scalar> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<scalar>
     *
     * @throws InvalidArgumentException
     */
    public static function allScalar(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::scalar($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<scalar|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<scalar|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrScalar(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::scalar($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert object|null $value
     *
     * @param string|callable():string $message
     *
     * @return object|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrObject(mixed $value, callable|string $message = ''): ?object
    {
        null === $value || static::object($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object>
     *
     * @throws InvalidArgumentException
     */
    public static function allObject(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::object($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrObject(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::object($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert object|class-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return object|class-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrObjectish(mixed $value, callable|string $message = ''): object|string|null
    {
        null === $value || static::objectish($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object|class-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object|class-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allObjectish(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::objectish($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<object|class-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<object|class-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrObjectish(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::objectish($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert resource|null $value
     *
     * @param string|callable():string $message
     *
     * @see https://www.php.net/manual/en/function.get-resource-type.php
     *
     * @return resource|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrResource(mixed $value, ?string $type = null, callable|string $message = ''): mixed
    {
        null === $value || static::resource($value, $type, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<resource> $value
     *
     * @param string|callable():string $message
     *
     * @see https://www.php.net/manual/en/function.get-resource-type.php
     *
     * @return iterable<resource>
     *
     * @throws InvalidArgumentException
     */
    public static function allResource(mixed $value, ?string $type = null, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::resource($entry, $type, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<resource|null> $value
     *
     * @param string|callable():string $message
     *
     * @see https://www.php.net/manual/en/function.get-resource-type.php
     *
     * @return iterable<resource|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrResource(mixed $value, ?string $type = null, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::resource($entry, $type, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert callable|null $value
     *
     * @param string|callable():string $message
     *
     * @return callable|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsCallable(mixed $value, callable|string $message = ''): ?callable
    {
        null === $value || static::isCallable($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<callable> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<callable>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsCallable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isCallable($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<callable|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<callable|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsCallable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isCallable($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert array|null $value
     *
     * @param string|callable():string $message
     *
     * @return array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsArray(mixed $value, callable|string $message = ''): ?array
    {
        null === $value || static::isArray($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsArray(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isArray($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsArray(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isArray($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert array|ArrayAccess|null $value
     *
     * @param string|callable():string $message
     *
     * @return array|ArrayAccess|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsArrayAccessible(mixed $value, callable|string $message = ''): ArrayAccess|array|null
    {
        null === $value || static::isArrayAccessible($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array|ArrayAccess> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array|ArrayAccess>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsArrayAccessible(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isArrayAccessible($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array|ArrayAccess|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array|ArrayAccess|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsArrayAccessible(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isArrayAccessible($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert countable|null $value
     *
     * @param string|callable():string $message
     *
     * @return countable|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsCountable(mixed $value, callable|string $message = ''): Countable|array|null
    {
        null === $value || static::isCountable($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<countable> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<countable>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsCountable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isCountable($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<countable|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<countable|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsCountable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isCountable($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable|null $value
     *
     * @param string|callable():string $message
     *
     * @return iterable|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsIterable(mixed $value, callable|string $message = ''): ?iterable
    {
        null === $value || static::isIterable($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<iterable> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<iterable>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsIterable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isIterable($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<iterable|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<iterable|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsIterable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isIterable($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert T|null $value
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsInstanceOf(mixed $value, mixed $class, callable|string $message = ''): ?object
    {
        null === $value || static::isInstanceOf($value, $class, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert iterable<T> $value
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsInstanceOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isInstanceOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T of object|null
     * @psalm-assert iterable<T> $value
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsInstanceOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isInstanceOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @template T of object
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return object|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotInstanceOf(mixed $value, mixed $class, callable|string $message = ''): ?object
    {
        null === $value || static::notInstanceOf($value, $class, $message);

        return $value;
    }

    /**
     * @template T of object
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return iterable<object>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotInstanceOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notInstanceOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @template T of object|null
     * @psalm-assert iterable<object|null> $value
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return iterable<object|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotInstanceOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notInstanceOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @template T of object
     * @psalm-assert T|null $value
     *
     * @param iterable<class-string<T>> $classes
     * @param string|callable():string  $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsInstanceOfAny(mixed $value, mixed $classes, callable|string $message = ''): ?object
    {
        null === $value || static::isInstanceOfAny($value, $classes, $message);

        return $value;
    }

    /**
     * @template T of object
     * @psalm-assert iterable<T> $value
     *
     * @param iterable<class-string<T>> $classes
     * @param string|callable():string  $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsInstanceOfAny(mixed $value, mixed $classes, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isInstanceOfAny($entry, $classes, $message);
        }

        return $value;
    }

    /**
     * @template T of object|null
     * @psalm-assert iterable<T> $value
     *
     * @param iterable<class-string<T>> $classes
     * @param string|callable():string  $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsInstanceOfAny(mixed $value, mixed $classes, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isInstanceOfAny($entry, $classes, $message);
        }

        return $value;
    }

    /**
     * @template T
     * @psalm-assert object|class-string|null $value
     *
     * @param T|null                   $value
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsNotInstanceOfAny(mixed $value, mixed $classes, callable|string $message = ''): mixed
    {
        null === $value || static::isNotInstanceOfAny($value, $classes, $message);

        return $value;
    }

    /**
     * @template T
     * @psalm-assert iterable<object|class-string> $value
     *
     * @param iterable<T>              $value
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsNotInstanceOfAny(mixed $value, mixed $classes, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isNotInstanceOfAny($entry, $classes, $message);
        }

        return $value;
    }

    /**
     * @template T
     * @psalm-assert iterable<object|class-string|null> $value
     *
     * @param iterable<T>              $value
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsNotInstanceOfAny(mixed $value, mixed $classes, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isNotInstanceOfAny($entry, $classes, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert T|class-string<T>|null $value
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return T|class-string<T>|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsAOf(mixed $value, mixed $class, callable|string $message = ''): object|string|null
    {
        null === $value || static::isAOf($value, $class, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T of object
     * @psalm-assert iterable<T|class-string<T>> $value
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return iterable<T|class-string<T>>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsAOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isAOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T of object|null
     * @psalm-assert iterable<T|class-string<T>|null> $value
     *
     * @param class-string<T>          $class
     * @param string|callable():string $message
     *
     * @return iterable<T|class-string<T>|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsAOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isAOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param T|null                   $value
     * @param string|callable():string $message
     *
     * @return object|class-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsNotA(mixed $value, mixed $class, callable|string $message = ''): object|string|null
    {
        null === $value || static::isNotA($value, $class, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param iterable<T>              $value
     * @param string|callable():string $message
     *
     * @return iterable<object|class-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsNotA(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isNotA($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<object|class-string|null> $value
     *
     * @param iterable<T>              $value
     * @param string|callable():string $message
     *
     * @return iterable<object|class-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsNotA(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isNotA($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert T|null $value
     *
     * @template T as object
     *
     * @param array<class-string<T>>   $classes
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsAnyOf(mixed $value, mixed $classes, callable|string $message = ''): object|string|null
    {
        null === $value || static::isAnyOf($value, $classes, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<T> $value
     *
     * @template T as object
     *
     * @param array<class-string<T>>   $classes
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsAnyOf(mixed $value, mixed $classes, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isAnyOf($entry, $classes, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<T> $value
     *
     * @template T as object|null
     *
     * @param array<class-string<T>>   $classes
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsAnyOf(mixed $value, mixed $classes, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isAnyOf($entry, $classes, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert empty $value
     *
     * @param string|callable():string $message
     *
     * @return empty
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsEmpty(mixed $value, callable|string $message = ''): mixed
    {
        null === $value || static::isEmpty($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<empty> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<empty>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsEmpty(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::isEmpty($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<empty|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<empty|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsEmpty(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::isEmpty($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotEmpty(mixed $value, callable|string $message = ''): mixed
    {
        null === $value || static::notEmpty($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNotEmpty(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notEmpty($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<!empty|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<!empty|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotEmpty(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notEmpty($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNull(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::null($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param iterable<T|null>         $value
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotNull(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notNull($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert true|null $value
     *
     * @param string|callable():string $message
     *
     * @return true|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrTrue(mixed $value, callable|string $message = ''): ?true
    {
        null === $value || static::true($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<true> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<true>
     *
     * @throws InvalidArgumentException
     */
    public static function allTrue(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::true($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<true|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<true|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrTrue(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::true($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert false|null $value
     *
     * @param string|callable():string $message
     *
     * @return false|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrFalse(mixed $value, callable|string $message = ''): ?false
    {
        null === $value || static::false($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<false> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<false>
     *
     * @throws InvalidArgumentException
     */
    public static function allFalse(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::false($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<false|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<false|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrFalse(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::false($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param T|false|null             $value
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotFalse(mixed $value, callable|string $message = ''): mixed
    {
        null === $value || static::notFalse($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param iterable<T|false>        $value
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotFalse(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notFalse($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<!false|null> $value
     *
     * @template T
     *
     * @param iterable<T|false|null>   $value
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotFalse(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notFalse($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|null              $value
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIp(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::ip($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string>         $value
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allIp(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::ip($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string|null>    $value
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIp(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::ip($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|null              $value
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIpv4(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::ipv4($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string>         $value
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allIpv4(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::ipv4($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string|null>    $value
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIpv4(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::ipv4($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|null              $value
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIpv6(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::ipv6($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string>         $value
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allIpv6(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::ipv6($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string|null>    $value
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIpv6(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::ipv6($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|null              $value
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrEmail(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::email($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string>         $value
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allEmail(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::email($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string|null>    $value
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrEmail(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::email($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-assert array|null $values
     *
     * @param string|callable():string $message
     *
     * @return array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrUniqueValues(mixed $values, callable|string $message = ''): ?array
    {
        null === $values || static::uniqueValues($values, $message);

        return $values;
    }

    /**
     * @psalm-assert iterable<array> $values
     *
     * @param string|callable():string $message
     *
     * @return iterable<array>
     *
     * @throws InvalidArgumentException
     */
    public static function allUniqueValues(mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($values);

        foreach ($values as $entry) {
            static::uniqueValues($entry, $message);
        }

        return $values;
    }

    /**
     * @psalm-assert iterable<array|null> $values
     *
     * @param string|callable():string $message
     *
     * @return iterable<array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrUniqueValues(mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($values);

        foreach ($values as $entry) {
            null === $entry || static::uniqueValues($entry, $message);
        }

        return $values;
    }

    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrEq(mixed $value, mixed $expect, callable|string $message = ''): mixed
    {
        null === $value || static::eq($value, $expect, $message);

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allEq(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::eq($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrEq(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::eq($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotEq(mixed $value, mixed $expect, callable|string $message = ''): mixed
    {
        null === $value || static::notEq($value, $expect, $message);

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNotEq(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notEq($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotEq(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notEq($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrSame(mixed $value, mixed $expect, callable|string $message = ''): mixed
    {
        null === $value || static::same($value, $expect, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allSame(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::same($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrSame(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::same($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotSame(mixed $value, mixed $expect, callable|string $message = ''): mixed
    {
        null === $value || static::notSame($value, $expect, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNotSame(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notSame($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotSame(mixed $value, mixed $expect, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notSame($entry, $expect, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrGreaterThan(mixed $value, mixed $limit, callable|string $message = ''): mixed
    {
        null === $value || static::greaterThan($value, $limit, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allGreaterThan(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::greaterThan($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrGreaterThan(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::greaterThan($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrGreaterThanEq(mixed $value, mixed $limit, callable|string $message = ''): mixed
    {
        null === $value || static::greaterThanEq($value, $limit, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allGreaterThanEq(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::greaterThanEq($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrGreaterThanEq(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::greaterThanEq($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrLessThan(mixed $value, mixed $limit, callable|string $message = ''): mixed
    {
        null === $value || static::lessThan($value, $limit, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allLessThan(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::lessThan($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrLessThan(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::lessThan($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrLessThanEq(mixed $value, mixed $limit, callable|string $message = ''): mixed
    {
        null === $value || static::lessThanEq($value, $limit, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allLessThanEq(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::lessThanEq($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrLessThanEq(mixed $value, mixed $limit, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::lessThanEq($entry, $limit, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrRange(mixed $value, mixed $min, mixed $max, callable|string $message = ''): mixed
    {
        null === $value || static::range($value, $min, $max, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allRange(mixed $value, mixed $min, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::range($entry, $min, $max, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrRange(mixed $value, mixed $min, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::range($entry, $min, $max, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrOneOf(mixed $value, mixed $values, callable|string $message = ''): mixed
    {
        null === $value || static::oneOf($value, $values, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allOneOf(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::oneOf($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrOneOf(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::oneOf($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrInArray(mixed $value, mixed $values, callable|string $message = ''): mixed
    {
        null === $value || static::inArray($value, $values, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allInArray(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::inArray($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrInArray(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::inArray($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotOneOf(mixed $value, mixed $values, callable|string $message = ''): mixed
    {
        null === $value || static::notOneOf($value, $values, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNotOneOf(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notOneOf($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotOneOf(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notOneOf($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotInArray(mixed $value, mixed $values, callable|string $message = ''): mixed
    {
        null === $value || static::notInArray($value, $values, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNotInArray(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notInArray($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotInArray(mixed $value, mixed $values, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notInArray($entry, $values, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrContains(mixed $value, mixed $subString, callable|string $message = ''): ?string
    {
        null === $value || static::contains($value, $subString, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allContains(mixed $value, mixed $subString, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::contains($entry, $subString, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrContains(mixed $value, mixed $subString, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::contains($entry, $subString, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotContains(mixed $value, mixed $subString, callable|string $message = ''): ?string
    {
        null === $value || static::notContains($value, $subString, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotContains(mixed $value, mixed $subString, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notContains($entry, $subString, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotContains(mixed $value, mixed $subString, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notContains($entry, $subString, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotWhitespaceOnly(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::notWhitespaceOnly($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotWhitespaceOnly(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notWhitespaceOnly($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotWhitespaceOnly(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notWhitespaceOnly($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrStartsWith(mixed $value, mixed $prefix, callable|string $message = ''): ?string
    {
        null === $value || static::startsWith($value, $prefix, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allStartsWith(mixed $value, mixed $prefix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::startsWith($entry, $prefix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrStartsWith(mixed $value, mixed $prefix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::startsWith($entry, $prefix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotStartsWith(mixed $value, mixed $prefix, callable|string $message = ''): ?string
    {
        null === $value || static::notStartsWith($value, $prefix, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotStartsWith(mixed $value, mixed $prefix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notStartsWith($entry, $prefix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotStartsWith(mixed $value, mixed $prefix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notStartsWith($entry, $prefix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrStartsWithLetter(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::startsWithLetter($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allStartsWithLetter(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::startsWithLetter($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrStartsWithLetter(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::startsWithLetter($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrEndsWith(mixed $value, mixed $suffix, callable|string $message = ''): ?string
    {
        null === $value || static::endsWith($value, $suffix, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allEndsWith(mixed $value, mixed $suffix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::endsWith($entry, $suffix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrEndsWith(mixed $value, mixed $suffix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::endsWith($entry, $suffix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotEndsWith(mixed $value, mixed $suffix, callable|string $message = ''): ?string
    {
        null === $value || static::notEndsWith($value, $suffix, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotEndsWith(mixed $value, mixed $suffix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notEndsWith($entry, $suffix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotEndsWith(mixed $value, mixed $suffix, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notEndsWith($entry, $suffix, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrRegex(mixed $value, mixed $pattern, callable|string $message = ''): ?string
    {
        null === $value || static::regex($value, $pattern, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allRegex(mixed $value, mixed $pattern, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::regex($entry, $pattern, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrRegex(mixed $value, mixed $pattern, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::regex($entry, $pattern, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotRegex(mixed $value, mixed $pattern, callable|string $message = ''): ?string
    {
        null === $value || static::notRegex($value, $pattern, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotRegex(mixed $value, mixed $pattern, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::notRegex($entry, $pattern, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotRegex(mixed $value, mixed $pattern, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::notRegex($entry, $pattern, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrUnicodeLetters(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::unicodeLetters($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allUnicodeLetters(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::unicodeLetters($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrUnicodeLetters(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::unicodeLetters($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrAlpha(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::alpha($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allAlpha(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::alpha($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrAlpha(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::alpha($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrDigits(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::digits($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allDigits(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::digits($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrDigits(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::digits($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrAlnum(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::alnum($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allAlnum(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::alnum($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrAlnum(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::alnum($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert lowercase-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return lowercase-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrLower(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::lower($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<lowercase-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<lowercase-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allLower(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::lower($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<lowercase-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<lowercase-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrLower(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::lower($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrUpper(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::upper($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allUpper(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::upper($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrUpper(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::upper($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrLength(mixed $value, mixed $length, callable|string $message = ''): ?string
    {
        null === $value || static::length($value, $length, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allLength(mixed $value, mixed $length, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::length($entry, $length, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrLength(mixed $value, mixed $length, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::length($entry, $length, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrMinLength(mixed $value, mixed $min, callable|string $message = ''): ?string
    {
        null === $value || static::minLength($value, $min, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allMinLength(mixed $value, mixed $min, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::minLength($entry, $min, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrMinLength(mixed $value, mixed $min, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::minLength($entry, $min, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrMaxLength(mixed $value, mixed $max, callable|string $message = ''): ?string
    {
        null === $value || static::maxLength($value, $max, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allMaxLength(mixed $value, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::maxLength($entry, $max, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrMaxLength(mixed $value, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::maxLength($entry, $max, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrLengthBetween(mixed $value, mixed $min, mixed $max, callable|string $message = ''): ?string
    {
        null === $value || static::lengthBetween($value, $min, $max, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allLengthBetween(mixed $value, mixed $min, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::lengthBetween($entry, $min, $max, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrLengthBetween(mixed $value, mixed $min, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::lengthBetween($entry, $min, $max, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrFileExists(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::fileExists($value, $message);

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allFileExists(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::fileExists($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrFileExists(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::fileExists($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrFile(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::file($value, $message);

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allFile(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::file($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrFile(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::file($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrDirectory(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::directory($value, $message);

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allDirectory(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::directory($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrDirectory(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::directory($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrReadable(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::readable($value, $message);

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allReadable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::readable($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrReadable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::readable($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrWritable(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::writable($value, $message);

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allWritable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::writable($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrWritable(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::writable($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-assert class-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return class-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrClassExists(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::classExists($value, $message);

        return $value;
    }

    /**
     * @psalm-assert iterable<class-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allClassExists(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::classExists($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-assert iterable<class-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrClassExists(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::classExists($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert class-string<ExpectedType>|null $value
     *
     * @param class-string<ExpectedType> $class
     * @param string|callable():string   $message
     *
     * @return class-string<ExpectedType>|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrSubclassOf(mixed $value, mixed $class, callable|string $message = ''): ?string
    {
        null === $value || static::subclassOf($value, $class, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert iterable<class-string<ExpectedType>> $value
     *
     * @param class-string<ExpectedType> $class
     * @param string|callable():string   $message
     *
     * @return iterable<class-string<ExpectedType>>
     *
     * @throws InvalidArgumentException
     */
    public static function allSubclassOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::subclassOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template ExpectedType of object|null
     * @psalm-assert iterable<class-string<ExpectedType>|null> $value
     *
     * @param class-string<ExpectedType> $class
     * @param string|callable():string   $message
     *
     * @return iterable<class-string<ExpectedType>|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrSubclassOf(mixed $value, mixed $class, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::subclassOf($entry, $class, $message);
        }

        return $value;
    }

    /**
     * @psalm-assert class-string|null $value
     *
     * @param string|callable():string $message
     *
     * @return class-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrInterfaceExists(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::interfaceExists($value, $message);

        return $value;
    }

    /**
     * @psalm-assert iterable<class-string> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allInterfaceExists(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::interfaceExists($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-assert iterable<class-string|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<class-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrInterfaceExists(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::interfaceExists($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert class-string<ExpectedType>|ExpectedType|null $value
     *
     * @param class-string<ExpectedType> $interface
     * @param string|callable():string   $message
     *
     * @return class-string<ExpectedType>|ExpectedType|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrImplementsInterface(mixed $value, mixed $interface, callable|string $message = ''): object|string|null
    {
        null === $value || static::implementsInterface($value, $interface, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template ExpectedType of object
     * @psalm-assert iterable<class-string<ExpectedType>|ExpectedType> $value
     *
     * @param class-string<ExpectedType> $interface
     * @param string|callable():string   $message
     *
     * @return iterable<class-string<ExpectedType>|ExpectedType>
     *
     * @throws InvalidArgumentException
     */
    public static function allImplementsInterface(mixed $value, mixed $interface, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::implementsInterface($entry, $interface, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @template ExpectedType of object|null
     * @psalm-assert iterable<class-string<ExpectedType>|ExpectedType|null> $value
     *
     * @param class-string<ExpectedType> $interface
     * @param string|callable():string   $message
     *
     * @return iterable<class-string<ExpectedType>|ExpectedType|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrImplementsInterface(mixed $value, mixed $interface, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::implementsInterface($entry, $interface, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|object|null       $classOrObject
     * @param string|callable():string $message
     *
     * @return string|object|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrPropertyExists(mixed $classOrObject, mixed $property, callable|string $message = ''): object|string|null
    {
        null === $classOrObject || static::propertyExists($classOrObject, $property, $message);

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string|object>  $classOrObject
     * @param string|callable():string $message
     *
     * @return iterable<string|object>
     *
     * @throws InvalidArgumentException
     */
    public static function allPropertyExists(mixed $classOrObject, mixed $property, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            static::propertyExists($entry, $property, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @param iterable<string|object|null> $classOrObject
     * @param string|callable():string     $message
     *
     * @return iterable<string|object|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrPropertyExists(mixed $classOrObject, mixed $property, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            null === $entry || static::propertyExists($entry, $property, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object
     *
     * @param T|null                   $classOrObject
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrPropertyNotExists(mixed $classOrObject, mixed $property, callable|string $message = ''): mixed
    {
        null === $classOrObject || static::propertyNotExists($classOrObject, $property, $message);

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object
     *
     * @param iterable<T>              $classOrObject
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allPropertyNotExists(mixed $classOrObject, mixed $property, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            static::propertyNotExists($entry, $property, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object|null
     *
     * @param iterable<T>              $classOrObject
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrPropertyNotExists(mixed $classOrObject, mixed $property, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            null === $entry || static::propertyNotExists($entry, $property, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object
     *
     * @param T|null                   $classOrObject
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrMethodExists(mixed $classOrObject, mixed $method, callable|string $message = ''): object|string|null
    {
        null === $classOrObject || static::methodExists($classOrObject, $method, $message);

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object
     *
     * @param iterable<T>              $classOrObject
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allMethodExists(mixed $classOrObject, mixed $method, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            static::methodExists($entry, $method, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object|null
     *
     * @param iterable<T>              $classOrObject
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrMethodExists(mixed $classOrObject, mixed $method, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            null === $entry || static::methodExists($entry, $method, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object
     *
     * @param T|null                   $classOrObject
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrMethodNotExists(mixed $classOrObject, mixed $method, callable|string $message = ''): mixed
    {
        null === $classOrObject || static::methodNotExists($classOrObject, $method, $message);

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object
     *
     * @param iterable<T>              $classOrObject
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allMethodNotExists(mixed $classOrObject, mixed $method, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            static::methodNotExists($entry, $method, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @template T as class-string|object|null
     *
     * @param iterable<T>              $classOrObject
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrMethodNotExists(mixed $classOrObject, mixed $method, callable|string $message = ''): iterable
    {
        static::isIterable($classOrObject);

        foreach ($classOrObject as $entry) {
            null === $entry || static::methodNotExists($entry, $method, $message);
        }

        return $classOrObject;
    }

    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrKeyExists(mixed $array, string|int $key, callable|string $message = ''): ?array
    {
        null === $array || static::keyExists($array, $key, $message);

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return iterable<array>
     *
     * @throws InvalidArgumentException
     */
    public static function allKeyExists(mixed $array, string|int $key, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::keyExists($entry, $key, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return iterable<array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrKeyExists(mixed $array, string|int $key, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::keyExists($entry, $key, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrKeyNotExists(mixed $array, string|int $key, callable|string $message = ''): ?array
    {
        null === $array || static::keyNotExists($array, $key, $message);

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return iterable<array>
     *
     * @throws InvalidArgumentException
     */
    public static function allKeyNotExists(mixed $array, string|int $key, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::keyNotExists($entry, $key, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @param string|int               $key
     * @param string|callable():string $message
     *
     * @return iterable<array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrKeyNotExists(mixed $array, string|int $key, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::keyNotExists($entry, $key, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert array-key|null $value
     *
     * @param string|callable():string $message
     *
     * @return array-key|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrValidArrayKey(mixed $value, callable|string $message = ''): string|int|null
    {
        null === $value || static::validArrayKey($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array-key> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array-key>
     *
     * @throws InvalidArgumentException
     */
    public static function allValidArrayKey(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::validArrayKey($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<array-key|null> $value
     *
     * @param string|callable():string $message
     *
     * @return iterable<array-key|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrValidArrayKey(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::validArrayKey($entry, $message);
        }

        return $value;
    }

    /**
     * @param string|callable():string $message
     *
     * @return Countable|array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrCount(mixed $array, mixed $number, callable|string $message = ''): Countable|array|null
    {
        null === $array || static::count($array, $number, $message);

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array>
     *
     * @throws InvalidArgumentException
     */
    public static function allCount(mixed $array, mixed $number, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::count($entry, $number, $message);
        }

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrCount(mixed $array, mixed $number, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::count($entry, $number, $message);
        }

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return Countable|array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrMinCount(mixed $array, mixed $min, callable|string $message = ''): Countable|array|null
    {
        null === $array || static::minCount($array, $min, $message);

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array>
     *
     * @throws InvalidArgumentException
     */
    public static function allMinCount(mixed $array, mixed $min, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::minCount($entry, $min, $message);
        }

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrMinCount(mixed $array, mixed $min, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::minCount($entry, $min, $message);
        }

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return Countable|array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrMaxCount(mixed $array, mixed $max, callable|string $message = ''): Countable|array|null
    {
        null === $array || static::maxCount($array, $max, $message);

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array>
     *
     * @throws InvalidArgumentException
     */
    public static function allMaxCount(mixed $array, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::maxCount($entry, $max, $message);
        }

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrMaxCount(mixed $array, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::maxCount($entry, $max, $message);
        }

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return Countable|array|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrCountBetween(mixed $array, mixed $min, mixed $max, callable|string $message = ''): Countable|array|null
    {
        null === $array || static::countBetween($array, $min, $max, $message);

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array>
     *
     * @throws InvalidArgumentException
     */
    public static function allCountBetween(mixed $array, mixed $min, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::countBetween($entry, $min, $max, $message);
        }

        return $array;
    }

    /**
     * @param string|callable():string $message
     *
     * @return iterable<Countable|array|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrCountBetween(mixed $array, mixed $min, mixed $max, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::countBetween($entry, $min, $max, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert list<mixed>|null $array
     *
     * @param string|callable():string $message
     *
     * @return list<mixed>|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsList(mixed $array, callable|string $message = ''): ?array
    {
        null === $array || static::isList($array, $message);

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<list<mixed>> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<list<mixed>>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsList(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::isList($entry, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<list<mixed>|null> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<list<mixed>|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsList(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::isList($entry, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert non-empty-list<mixed>|null $array
     *
     * @param string|callable():string $message
     *
     * @return non-empty-list<mixed>|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsNonEmptyList(mixed $array, callable|string $message = ''): ?array
    {
        null === $array || static::isNonEmptyList($array, $message);

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-list<mixed>> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-list<mixed>>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsNonEmptyList(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::isNonEmptyList($entry, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @psalm-assert iterable<non-empty-list<mixed>|null> $array
     *
     * @param string|callable():string $message
     *
     * @return iterable<non-empty-list<mixed>|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsNonEmptyList(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::isNonEmptyList($entry, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert array<string, T>|null $array
     *
     * @param mixed|array<array-key, T>|null $array
     * @param string|callable():string       $message
     *
     * @return array<string, T>|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsMap(mixed $array, callable|string $message = ''): ?array
    {
        null === $array || static::isMap($array, $message);

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<array<string, T>> $array
     *
     * @param iterable<mixed|array<array-key, T>> $array
     * @param string|callable():string            $message
     *
     * @return iterable<array<string, T>>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsMap(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::isMap($entry, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<array<string, T>|null> $array
     *
     * @param iterable<mixed|array<array-key, T>|null> $array
     * @param string|callable():string                 $message
     *
     * @return iterable<array<string, T>|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsMap(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::isMap($entry, $message);
        }

        return $array;
    }

    /**
     * @param callable|null            $callable
     * @param string|callable():string $message
     *
     * @return Closure|callable-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsStatic(mixed $callable, callable|string $message = ''): Closure|string|null
    {
        null === $callable || static::isStatic($callable, $message);

        return $callable;
    }

    /**
     * @param iterable<callable>       $callable
     * @param string|callable():string $message
     *
     * @return iterable<Closure|callable-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsStatic(mixed $callable, callable|string $message = ''): iterable
    {
        static::isIterable($callable);

        foreach ($callable as $entry) {
            static::isStatic($entry, $message);
        }

        return $callable;
    }

    /**
     * @param iterable<callable|null>  $callable
     * @param string|callable():string $message
     *
     * @return iterable<Closure|callable-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsStatic(mixed $callable, callable|string $message = ''): iterable
    {
        static::isIterable($callable);

        foreach ($callable as $entry) {
            null === $entry || static::isStatic($entry, $message);
        }

        return $callable;
    }

    /**
     * @param callable|null            $callable
     * @param string|callable():string $message
     *
     * @return Closure|callable-string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrNotStatic(mixed $callable, callable|string $message = ''): Closure|string|null
    {
        null === $callable || static::notStatic($callable, $message);

        return $callable;
    }

    /**
     * @param iterable<callable>       $callable
     * @param string|callable():string $message
     *
     * @return iterable<Closure|callable-string>
     *
     * @throws InvalidArgumentException
     */
    public static function allNotStatic(mixed $callable, callable|string $message = ''): iterable
    {
        static::isIterable($callable);

        foreach ($callable as $entry) {
            static::notStatic($entry, $message);
        }

        return $callable;
    }

    /**
     * @param iterable<callable|null>  $callable
     * @param string|callable():string $message
     *
     * @return iterable<Closure|callable-string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrNotStatic(mixed $callable, callable|string $message = ''): iterable
    {
        static::isIterable($callable);

        foreach ($callable as $entry) {
            null === $entry || static::notStatic($entry, $message);
        }

        return $callable;
    }

    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param array<string, T>|null    $array
     * @param string|callable():string $message
     *
     * @return non-empty-array<string, T>|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrIsNonEmptyMap(mixed $array, callable|string $message = ''): ?array
    {
        null === $array || static::isNonEmptyMap($array, $message);

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @template T
     *
     * @param iterable<array<string, T>> $array
     * @param string|callable():string   $message
     *
     * @return iterable<non-empty-array<string, T>>
     *
     * @throws InvalidArgumentException
     */
    public static function allIsNonEmptyMap(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            static::isNonEmptyMap($entry, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @template T
     * @psalm-assert iterable<array<string, T>|null> $array
     * @psalm-assert iterable<!empty|null> $array
     *
     * @param iterable<array<string, T>|null> $array
     * @param string|callable():string        $message
     *
     * @return iterable<non-empty-array<string, T>|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrIsNonEmptyMap(mixed $array, callable|string $message = ''): iterable
    {
        static::isIterable($array);

        foreach ($array as $entry) {
            null === $entry || static::isNonEmptyMap($entry, $message);
        }

        return $array;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return string|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrUuid(mixed $value, callable|string $message = ''): ?string
    {
        null === $value || static::uuid($value, $message);

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string>
     *
     * @throws InvalidArgumentException
     */
    public static function allUuid(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            static::uuid($entry, $message);
        }

        return $value;
    }

    /**
     * @psalm-pure
     *
     * @param string|callable():string $message
     *
     * @return iterable<string|null>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrUuid(mixed $value, callable|string $message = ''): iterable
    {
        static::isIterable($value);

        foreach ($value as $entry) {
            null === $entry || static::uuid($entry, $message);
        }

        return $value;
    }

    /**
     * @template T as callable
     *
     * @param T|null                   $expression
     * @param class-string<Throwable>  $class
     * @param string|callable():string $message
     *
     * @return T|null
     *
     * @throws InvalidArgumentException
     */
    public static function nullOrThrows(mixed $expression, string $class = 'Throwable', callable|string $message = ''): ?callable
    {
        null === $expression || static::throws($expression, $class, $message);

        return $expression;
    }

    /**
     * @template T as callable
     *
     * @param iterable<T>              $expression
     * @param class-string<Throwable>  $class
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allThrows(mixed $expression, string $class = 'Throwable', callable|string $message = ''): iterable
    {
        static::isIterable($expression);

        foreach ($expression as $entry) {
            static::throws($entry, $class, $message);
        }

        return $expression;
    }

    /**
     * @template T as callable|null
     *
     * @param iterable<T>              $expression
     * @param class-string<Throwable>  $class
     * @param string|callable():string $message
     *
     * @return iterable<T>
     *
     * @throws InvalidArgumentException
     */
    public static function allNullOrThrows(mixed $expression, string $class = 'Throwable', callable|string $message = ''): iterable
    {
        static::isIterable($expression);

        foreach ($expression as $entry) {
            null === $entry || static::throws($entry, $class, $message);
        }

        return $expression;
    }
}
