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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestDoubleState
{
    private static array $deprecationEmittedForTest = [];

    /**
     * @psalm-var list<ConfigurableMethod>
     */
    private readonly array $configurableMethods;
    private readonly bool $generateReturnValues;
    private ?InvocationHandler $invocationHandler = null;
    private ?object $proxyTarget                  = null;

    /**
     * @psalm-param list<ConfigurableMethod> $configurableMethods
     */
    public function __construct(array $configurableMethods, bool $generateReturnValues)
    {
        $this->configurableMethods  = $configurableMethods;
        $this->generateReturnValues = $generateReturnValues;
    }

    public function invocationHandler(): InvocationHandler
    {
        if ($this->invocationHandler !== null) {
            return $this->invocationHandler;
        }

        $this->invocationHandler = new InvocationHandler(
            $this->configurableMethods,
            $this->generateReturnValues,
        );

        return $this->invocationHandler;
    }

    public function cloneInvocationHandler(): void
    {
        if ($this->invocationHandler === null) {
            return;
        }

        $this->invocationHandler = clone $this->invocationHandler;
    }

    public function unsetInvocationHandler(): void
    {
        $this->invocationHandler = null;
    }

    public function setProxyTarget(object $proxyTarget): void
    {
        $this->proxyTarget = $proxyTarget;
    }

    public function proxyTarget(): object
    {
        assert($this->proxyTarget !== null);

        return $this->proxyTarget;
    }

    public function deprecationWasEmittedFor(string $testId): void
    {
        self::$deprecationEmittedForTest[$testId] = true;
    }

    public function wasDeprecationAlreadyEmittedFor(string $testId): bool
    {
        return isset(self::$deprecationEmittedForTest[$testId]);
    }
}
