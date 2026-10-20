<?php

namespace React\Promise;

use React\Promise\Exception\CompositeException;
use React\Promise\Internal\FulfilledPromise;
use React\Promise\Internal\RejectedPromise;

/**
 * Creates a promise for the supplied `$promiseOrValue`.
 *
 * If `$promiseOrValue` is a value, it will be the resolution value of the
 * returned promise.
 *
 * If `$promiseOrValue` is a thenable (any object that provides a `then()` method),
 * a trusted promise that follows the state of the thenable is returned.
 *
 * If `$promiseOrValue` is a promise, it will be returned as is.
 *
 * @template T
 * @param PromiseInterface<T>|T $promiseOrValue
 * @return PromiseInterface<T>
 */
function resolve($promiseOrValue): PromiseInterface
{
    if ($promiseOrValue instanceof PromiseInterface) {
        return $promiseOrValue;
    }

    if (\is_object($promiseOrValue) && \method_exists($promiseOrValue, 'then')) {
        $canceller = null;

        if (\method_exists($promiseOrValue, 'cancel')) {
            $canceller = [$promiseOrValue, 'cancel'];
            assert(\is_callable($canceller));
        }

        /** @var Promise<T> */
        return new Promise(function (callable $resolve, callable $reject) use ($promiseOrValue): void {
            $promiseOrValue->then($resolve, $reject);
        }, $canceller);
    }

    return new FulfilledPromise($promiseOrValue);
}

/**
 * Creates a rejected promise for the supplied `$reason`.
 *
 * If `$reason` is a value, it will be the rejection value of the
 * returned promise.
 *
 * If `$reason` is a promise, its completion value will be the rejected
 * value of the returned promise.
 *
 * This can be useful in situations where you need to reject a promise without
 * throwing an exception. For example, it allows you to propagate a rejection with
 * the value of another promise.
 *
 * @return PromiseInterface<never>
 */
function reject(\Throwable $reason): PromiseInterface
{
    return new RejectedPromise($reason);
}

/**
 * Returns a promise that will resolve only once all the items in
 * `$promisesOrValues` have resolved. The resolution value of the returned promise
 * will be an array containing the resolution values of each of the items in
 * `$promisesOrValues`.
 *
 * @template T
 * @param iterable<PromiseInterface<T>|T> $promisesOrValues
 * @return PromiseInterface<array<T>>
 */
function all(iterable $promisesOrValues): PromiseInterface
{
    $cancellationQueue = new Internal\CancellationQueue();

    /** @var Promise<array<T>> */
    return new Promise(function (callable $resolve, callable $reject) use ($promisesOrValues, $cancellationQueue): void {
        $toResolve = 0;
        /** @var bool */
        $continue  = true;
        $values    = [];

        foreach ($promisesOrValues as $i => $promiseOrValue) {
            $cancellationQueue->enqueue($promiseOrValue);
            $values[$i] = null;
            ++$toResolve;

            resolve($promiseOrValue)->then(
                function ($value) use ($i, &$values, &$toResolve, &$continue, $resolve): void {
                    $values[$i] = $value;

                    if (0 === --$toResolve && !$continue) {
                        $resolve($values);
                    }
                },
                function (\Throwable $reason) use (&$continue, $reject): void {
                    $continue = false;
                    $reject($reason);
                }
            );

            if (!$continue && !\is_array($promisesOrValues)) {
                break;
            }
        }

        $continue = false;
        if ($toResolve === 0) {
            $resolve($values);
        }
    }, $cancellationQueue);
}

/**
 * Initiates a competitive race that allows one winner. Returns a promise which is
 * resolved in the same way the first settled promise resolves.
 *
 * The returned promise will become **infinitely pending** if  `$promisesOrValues`
 * contains 0 items.
 *
 * @template T
 * @param iterable<PromiseInterface<T>|T> $promisesOrValues
 * @return PromiseInterface<T>
 */
function race(iterable $promisesOrValues): PromiseInterface
{
    $cancellationQueue = new Internal\CancellationQueue();

    /** @var Promise<T> */
    return new Promise(function (callable $resolve, callable $reject) use ($promisesOrValues, $cancellationQueue): void {
        $continue = true;

        foreach ($promisesOrValues as $promiseOrValue) {
            $cancellationQueue->enqueue($promiseOrValue);

            resolve($promiseOrValue)->then($resolve, $reject)->finally(function () use (&$continue): void {
                $continue = false;
            });

            if (!$continue && !\is_array($promisesOrValues)) {
                break;
            }
        }
    }, $cancellationQueue);
}

/**
 * Returns a promise that will resolve when any one of the items in
 * `$promisesOrValues` resolves. The resolution value of the returned promise
 * will be the resolution value of the triggering item.
 *
 * The returned promise will only reject if *all* items in `$promisesOrValues` are
 * rejected. The rejection value will be an array of all rejection reasons.
 *
 * The returned promise will also reject with a `React\Promise\Exception\LengthException`
 * if `$promisesOrValues` contains 0 items.
 *
 * @template T
 * @param iterable<PromiseInterface<T>|T> $promisesOrValues
 * @return PromiseInterface<T>
 */
function any(iterable $promisesOrValues): PromiseInterface
{
    $cancellationQueue = new Internal\CancellationQueue();

    /** @var Promise<T> */
    return new Promise(function (callable $resolve, callable $reject) use ($promisesOrValues, $cancellationQueue): void {
        $toReject = 0;
        $continue = true;
        $reasons  = [];

        foreach ($promisesOrValues as $i => $promiseOrValue) {
            $cancellationQueue->enqueue($promiseOrValue);
            ++$toReject;

            resolve($promiseOrValue)->then(
                function ($value) use ($resolve, &$continue): void {
                    $continue = false;
                    $resolve($value);
                },
                function (\Throwable $reason) use ($i, &$reasons, &$toReject, $reject, &$continue): void {
                    $reasons[$i] = $reason;

                    if (0 === --$toReject && !$continue) {
                        $reject(new CompositeException(
                            $reasons,
                            'All promises rejected.'
                        ));
                    }
                }
            );

            if (!$continue && !\is_array($promisesOrValues)) {
                break;
            }
        }

        $continue = false;
        if ($toReject === 0 && !$reasons) {
            $reject(new Exception\LengthException(
                'Must contain at least 1 item but contains only 0 items.'
            ));
        } elseif ($toReject === 0) {
            $reject(new CompositeException(
                $reasons,
                'All promises rejected.'
            ));
        }
    }, $cancellationQueue);
}

/**
 * Sets the global rejection handler for unhandled promise rejections.
 *
 * Note that rejected promises should always be handled similar to how any
 * exceptions should always be caught in a `try` + `catch` block. If you remove
 * the last reference to a rejected promise that has not been handled, it will
 * report an unhandled promise rejection. See also the [`reject()` function](#reject)
 * for more details.
 *
 * The `?callable $callback` argument MUST be a valid callback function that
 * accepts a single `Throwable` argument or a `null` value to restore the
 * default promise rejection handler. The return value of the callback function
 * will be ignored and has no effect, so you SHOULD return a `void` value. The
 * callback function MUST NOT throw or the program will be terminated with a
 * fatal error.
 *
 * The function returns the previous rejection handler or `null` if using the
 * default promise rejection handler.
 *
 * The default promise rejection handler will log an error message plus its
 * stack trace:
 *
 * ```php
 * // Unhandled promise rejection with RuntimeException: Unhandled in example.php:2
 * React\Promise\reject(new RuntimeException('Unhandled'));
 * ```
 *
 * The promise rejection handler may be used to use customize the log message or
 * write to custom log targets. As a rule of thumb, this function should only be
 * used as a last resort and promise rejections are best handled with either the
 * [`then()` method](#promiseinterfacethen), the
 * [`catch()` method](#promiseinterfacecatch), or the
 * [`finally()` method](#promiseinterfacefinally).
 * See also the [`reject()` function](#reject) for more details.
 *
 * @param callable(\Throwable):void|null $callback
 * @return callable(\Throwable):void|null
 */
function set_rejection_handler(?callable $callback): ?callable
{
    static $current = null;
    $previous = $current;
    $current = $callback;

    return $previous;
}

/**
 * @internal
 */
function _checkTypehint(callable $callback, \Throwable $reason): bool
{
    if (\is_array($callback)) {
        $callbackReflection = new \ReflectionMethod($callback[0], $callback[1]);
    } elseif (\is_object($callback) && !$callback instanceof \Closure) {
        $callbackReflection = new \ReflectionMethod($callback, '__invoke');
    } else {
        assert($callback instanceof \Closure || \is_string($callback));
        $callbackReflection = new \ReflectionFunction($callback);
    }

    $parameters = $callbackReflection->getParameters();

    if (!isset($parameters[0])) {
        return true;
    }

    $expectedException = $parameters[0];

    // Extract the type of the argument and handle different possibilities
    $type = $expectedException->getType();

    $isTypeUnion = true;
    $types = [];

    switch (true) {
        case $type === null:
            break;
        case $type instanceof \ReflectionNamedType:
            $types = [$type];
            break;
        case $type instanceof \ReflectionIntersectionType:
            $isTypeUnion = false;
        case $type instanceof \ReflectionUnionType:
            $types = $type->getTypes();
            break;
        default:
            throw new \LogicException('Unexpected return value of ReflectionParameter::getType');
    }

    // If there is no type restriction, it matches
    if (empty($types)) {
        return true;
    }

    foreach ($types as $type) {

        if ($type instanceof \ReflectionIntersectionType) {
            foreach ($type->getTypes() as $typeToMatch) {
                assert($typeToMatch instanceof \ReflectionNamedType);
                $name = $typeToMatch->getName();
                if (!($matches = (!$typeToMatch->isBuiltin() && $reason instanceof $name))) {
                    break;
                }
            }
            assert(isset($matches));
        } else {
            assert($type instanceof \ReflectionNamedType);
            $name = $type->getName();
            $matches = !$type->isBuiltin() && $reason instanceof $name;
        }

        // If we look for a single match (union), we can return early on match
        // If we look for a full match (intersection), we can return early on mismatch
        if ($matches) {
            if ($isTypeUnion) {
                return true;
            }
        } else {
            if (!$isTypeUnion) {
                return false;
            }
        }
    }

    // If we look for a single match (union) and did not return early, we matched no type and are false
    // If we look for a full match (intersection) and did not return early, we matched all types and are true
    return $isTypeUnion ? false : true;
}
