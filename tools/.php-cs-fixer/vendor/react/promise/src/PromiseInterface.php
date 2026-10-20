<?php

namespace React\Promise;

/**
 * @template-covariant T
 */
interface PromiseInterface
{
    /**
     * Transforms a promise's value by applying a function to the promise's fulfillment
     * or rejection value. Returns a new promise for the transformed result.
     *
     * The `then()` method registers new fulfilled and rejection handlers with a promise
     * (all parameters are optional):
     *
     *  * `$onFulfilled` will be invoked once the promise is fulfilled and passed
     *     the result as the first argument.
     *  * `$onRejected` will be invoked once the promise is rejected and passed the
     *     reason as the first argument.
     *
     * It returns a new promise that will fulfill with the return value of either
     * `$onFulfilled` or `$onRejected`, whichever is called, or will reject with
     * the thrown exception if either throws.
     *
     * A promise makes the following guarantees about handlers registered in
     * the same call to `then()`:
     *
     *  1. Only one of `$onFulfilled` or `$onRejected` will be called,
     *      never both.
     *  2. `$onFulfilled` and `$onRejected` will never be called more
     *      than once.
     *
     * @template TFulfilled
     * @template TRejected
     * @param ?(callable((T is void ? null : T)): (PromiseInterface<TFulfilled>|TFulfilled)) $onFulfilled
     * @param ?(callable(\Throwable): (PromiseInterface<TRejected>|TRejected)) $onRejected
     * @return PromiseInterface<($onRejected is null ? ($onFulfilled is null ? T : TFulfilled) : ($onFulfilled is null ? T|TRejected : TFulfilled|TRejected))>
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface;

    /**
     * Registers a rejection handler for promise. It is a shortcut for:
     *
     * ```php
     * $promise->then(null, $onRejected);
     * ```
     *
     * Additionally, you can type hint the `$reason` argument of `$onRejected` to catch
     * only specific errors.
     *
     * @template TThrowable of \Throwable
     * @template TRejected
     * @param callable(TThrowable): (PromiseInterface<TRejected>|TRejected) $onRejected
     * @return PromiseInterface<T|TRejected>
     */
    public function catch(callable $onRejected): PromiseInterface;

    /**
     * Allows you to execute "cleanup" type tasks in a promise chain.
     *
     * It arranges for `$onFulfilledOrRejected` to be called, with no arguments,
     * when the promise is either fulfilled or rejected.
     *
     * * If `$promise` fulfills, and `$onFulfilledOrRejected` returns successfully,
     *    `$newPromise` will fulfill with the same value as `$promise`.
     * * If `$promise` fulfills, and `$onFulfilledOrRejected` throws or returns a
     *    rejected promise, `$newPromise` will reject with the thrown exception or
     *    rejected promise's reason.
     * * If `$promise` rejects, and `$onFulfilledOrRejected` returns successfully,
     *    `$newPromise` will reject with the same reason as `$promise`.
     * * If `$promise` rejects, and `$onFulfilledOrRejected` throws or returns a
     *    rejected promise, `$newPromise` will reject with the thrown exception or
     *    rejected promise's reason.
     *
     * `finally()` behaves similarly to the synchronous finally statement. When combined
     * with `catch()`, `finally()` allows you to write code that is similar to the familiar
     * synchronous catch/finally pair.
     *
     * Consider the following synchronous code:
     *
     * ```php
     * try {
     *     return doSomething();
     * } catch(\Exception $e) {
     *     return handleError($e);
     * } finally {
     *     cleanup();
     * }
     * ```
     *
     * Similar asynchronous code (with `doSomething()` that returns a promise) can be
     * written:
     *
     * ```php
     * return doSomething()
     *     ->catch('handleError')
     *     ->finally('cleanup');
     * ```
     *
     * @param callable(): (void|PromiseInterface<void>) $onFulfilledOrRejected
     * @return PromiseInterface<T>
     */
    public function finally(callable $onFulfilledOrRejected): PromiseInterface;

    /**
     * The `cancel()` method notifies the creator of the promise that there is no
     * further interest in the results of the operation.
     *
     * Once a promise is settled (either fulfilled or rejected), calling `cancel()` on
     * a promise has no effect.
     *
     * @return void
     */
    public function cancel(): void;

    /**
     * [Deprecated] Registers a rejection handler for a promise.
     *
     * This method continues to exist only for BC reasons and to ease upgrading
     * between versions. It is an alias for:
     *
     * ```php
     * $promise->catch($onRejected);
     * ```
     *
     * @template TThrowable of \Throwable
     * @template TRejected
     * @param callable(TThrowable): (PromiseInterface<TRejected>|TRejected) $onRejected
     * @return PromiseInterface<T|TRejected>
     * @deprecated 3.0.0 Use catch() instead
     * @see self::catch()
     */
    public function otherwise(callable $onRejected): PromiseInterface;

    /**
     * [Deprecated] Allows you to execute "cleanup" type tasks in a promise chain.
     *
     * This method continues to exist only for BC reasons and to ease upgrading
     * between versions. It is an alias for:
     *
     * ```php
     * $promise->finally($onFulfilledOrRejected);
     * ```
     *
     * @param callable(): (void|PromiseInterface<void>) $onFulfilledOrRejected
     * @return PromiseInterface<T>
     * @deprecated 3.0.0 Use finally() instead
     * @see self::finally()
     */
    public function always(callable $onFulfilledOrRejected): PromiseInterface;
}
