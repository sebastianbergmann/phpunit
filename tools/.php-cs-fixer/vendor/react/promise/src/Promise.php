<?php

namespace React\Promise;

use React\Promise\Internal\RejectedPromise;

/**
 * @template T
 * @template-implements PromiseInterface<T>
 */
final class Promise implements PromiseInterface
{
    /** @var (callable(callable(T):void,callable(\Throwable):void):void)|null */
    private $canceller;

    /** @var ?PromiseInterface<T> */
    private $result;

    /** @var list<callable(PromiseInterface<T>):void> */
    private $handlers = [];

    /** @var int */
    private $requiredCancelRequests = 0;

    /** @var bool */
    private $cancelled = false;

    /**
     * @param callable(callable(T):void,callable(\Throwable):void):void $resolver
     * @param (callable(callable(T):void,callable(\Throwable):void):void)|null $canceller
     */
    public function __construct(callable $resolver, ?callable $canceller = null)
    {
        $this->canceller = $canceller;

        // Explicitly overwrite arguments with null values before invoking
        // resolver function. This ensure that these arguments do not show up
        // in the stack trace in PHP 7+ only.
        $cb = $resolver;
        $resolver = $canceller = null;
        $this->call($cb);
    }

    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface
    {
        if (null !== $this->result) {
            return $this->result->then($onFulfilled, $onRejected);
        }

        if (null === $this->canceller) {
            return new static($this->resolver($onFulfilled, $onRejected));
        }

        // This promise has a canceller, so we create a new child promise which
        // has a canceller that invokes the parent canceller if all other
        // followers are also cancelled. We keep a reference to this promise
        // instance for the static canceller function and clear this to avoid
        // keeping a cyclic reference between parent and follower.
        $parent = $this;
        ++$parent->requiredCancelRequests;

        return new static(
            $this->resolver($onFulfilled, $onRejected),
            static function () use (&$parent): void {
                assert($parent instanceof self);
                --$parent->requiredCancelRequests;

                if ($parent->requiredCancelRequests <= 0) {
                    $parent->cancel();
                }

                $parent = null;
            }
        );
    }

    /**
     * @template TThrowable of \Throwable
     * @template TRejected
     * @param callable(TThrowable): (PromiseInterface<TRejected>|TRejected) $onRejected
     * @return PromiseInterface<T|TRejected>
     */
    public function catch(callable $onRejected): PromiseInterface
    {
        return $this->then(null, static function (\Throwable $reason) use ($onRejected) {
            if (!_checkTypehint($onRejected, $reason)) {
                return new RejectedPromise($reason);
            }

            /**
             * @var callable(\Throwable):(PromiseInterface<TRejected>|TRejected) $onRejected
             */
            return $onRejected($reason);
        });
    }

    public function finally(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->then(static function ($value) use ($onFulfilledOrRejected): PromiseInterface {
            /** @var T $value */
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        }, static function (\Throwable $reason) use ($onFulfilledOrRejected): PromiseInterface {
            return resolve($onFulfilledOrRejected())->then(function () use ($reason): RejectedPromise {
                return new RejectedPromise($reason);
            });
        });
    }

    public function cancel(): void
    {
        $this->cancelled = true;
        $canceller = $this->canceller;
        $this->canceller = null;

        $parentCanceller = null;

        if (null !== $this->result) {
            // Forward cancellation to rejected promise to avoid reporting unhandled rejection
            if ($this->result instanceof RejectedPromise) {
                $this->result->cancel();
            }

            // Go up the promise chain and reach the top most promise which is
            // itself not following another promise
            $root = $this->unwrap($this->result);

            // Return if the root promise is already resolved or a
            // FulfilledPromise or RejectedPromise
            if (!$root instanceof self || null !== $root->result) {
                return;
            }

            $root->requiredCancelRequests--;

            if ($root->requiredCancelRequests <= 0) {
                $parentCanceller = [$root, 'cancel'];
            }
        }

        if (null !== $canceller) {
            $this->call($canceller);
        }

        // For BC, we call the parent canceller after our own canceller
        if ($parentCanceller) {
            $parentCanceller();
        }
    }

    /**
     * @deprecated 3.0.0 Use `catch()` instead
     * @see self::catch()
     */
    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this->catch($onRejected);
    }

    /**
     * @deprecated 3.0.0 Use `finally()` instead
     * @see self::finally()
     */
    public function always(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->finally($onFulfilledOrRejected);
    }

    private function resolver(?callable $onFulfilled = null, ?callable $onRejected = null): callable
    {
        return function (callable $resolve, callable $reject) use ($onFulfilled, $onRejected): void {
            $this->handlers[] = static function (PromiseInterface $promise) use ($onFulfilled, $onRejected, $resolve, $reject): void {
                $promise = $promise->then($onFulfilled, $onRejected);

                if ($promise instanceof self && $promise->result === null) {
                    $promise->handlers[] = static function (PromiseInterface $promise) use ($resolve, $reject): void {
                        $promise->then($resolve, $reject);
                    };
                } else {
                    $promise->then($resolve, $reject);
                }
            };
        };
    }

    private function reject(\Throwable $reason): void
    {
        if (null !== $this->result) {
            return;
        }

        $this->settle(reject($reason));
    }

    /**
     * @param PromiseInterface<T> $result
     */
    private function settle(PromiseInterface $result): void
    {
        $result = $this->unwrap($result);

        if ($result === $this) {
            $result = new RejectedPromise(
                new \LogicException('Cannot resolve a promise with itself.')
            );
        }

        if ($result instanceof self) {
            $result->requiredCancelRequests++;
        } else {
            // Unset canceller only when not following a pending promise
            $this->canceller = null;
        }

        $handlers = $this->handlers;

        $this->handlers = [];
        $this->result = $result;

        foreach ($handlers as $handler) {
            $handler($result);
        }

        // Forward cancellation to rejected promise to avoid reporting unhandled rejection
        if ($this->cancelled && $result instanceof RejectedPromise) {
            $result->cancel();
        }
    }

    /**
     * @param PromiseInterface<T> $promise
     * @return PromiseInterface<T>
     */
    private function unwrap(PromiseInterface $promise): PromiseInterface
    {
        while ($promise instanceof self && null !== $promise->result) {
            /** @var PromiseInterface<T> $promise */
            $promise = $promise->result;
        }

        return $promise;
    }

    /**
     * @param callable(callable(mixed):void,callable(\Throwable):void):void $cb
     */
    private function call(callable $cb): void
    {
        // Explicitly overwrite argument with null value. This ensure that this
        // argument does not show up in the stack trace in PHP 7+ only.
        $callback = $cb;
        $cb = null;

        // Use reflection to inspect number of arguments expected by this callback.
        // We did some careful benchmarking here: Using reflection to avoid unneeded
        // function arguments is actually faster than blindly passing them.
        // Also, this helps avoiding unnecessary function arguments in the call stack
        // if the callback creates an Exception (creating garbage cycles).
        if (\is_array($callback)) {
            $ref = new \ReflectionMethod($callback[0], $callback[1]);
        } elseif (\is_object($callback) && !$callback instanceof \Closure) {
            $ref = new \ReflectionMethod($callback, '__invoke');
        } else {
            assert($callback instanceof \Closure || \is_string($callback));
            $ref = new \ReflectionFunction($callback);
        }
        $args = $ref->getNumberOfParameters();

        try {
            if ($args === 0) {
                $callback();
            } else {
                // Keep references to this promise instance for the static resolve/reject functions.
                // By using static callbacks that are not bound to this instance
                // and passing the target promise instance by reference, we can
                // still execute its resolving logic and still clear this
                // reference when settling the promise. This helps avoiding
                // garbage cycles if any callback creates an Exception.
                // These assumptions are covered by the test suite, so if you ever feel like
                // refactoring this, go ahead, any alternative suggestions are welcome!
                $target =& $this;

                $callback(
                    static function ($value) use (&$target): void {
                        if ($target !== null) {
                            $target->settle(resolve($value));
                            $target = null;
                        }
                    },
                    static function (\Throwable $reason) use (&$target): void {
                        if ($target !== null) {
                            $target->reject($reason);
                            $target = null;
                        }
                    }
                );
            }
        } catch (\Throwable $e) {
            $target = null;
            $this->reject($e);
        }
    }
}
