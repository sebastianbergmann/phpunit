<?php

namespace React\Promise\Internal;

use React\Promise\PromiseInterface;
use function React\Promise\resolve;

/**
 * @internal
 *
 * @template T
 * @template-implements PromiseInterface<T>
 */
final class FulfilledPromise implements PromiseInterface
{
    /** @var T */
    private $value;

    /**
     * @param T $value
     * @throws \InvalidArgumentException
     */
    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\Promise\FulfilledPromise with a promise. Use React\Promise\resolve($promiseOrValue) instead.');
        }

        $this->value = $value;
    }

    /**
     * @template TFulfilled
     * @param ?(callable((T is void ? null : T)): (PromiseInterface<TFulfilled>|TFulfilled)) $onFulfilled
     * @return PromiseInterface<($onFulfilled is null ? T : TFulfilled)>
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface
    {
        if (null === $onFulfilled) {
            return $this;
        }

        try {
            /**
             * @var PromiseInterface<T>|T $result
             */
            $result = $onFulfilled($this->value);
            return resolve($result);
        } catch (\Throwable $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function catch(callable $onRejected): PromiseInterface
    {
        return $this;
    }

    public function finally(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected): PromiseInterface {
            /** @var T $value */
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        });
    }

    public function cancel(): void
    {
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
}
