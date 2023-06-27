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

use WeakMap;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class InternalStubState
{
    private static ?self $instance = null;

    /**
     * @var array<class-string, list<ConfigurableMethod>>
     */
    private array $configurableMethods;

    /**
     * @var WeakMap<object, bool>
     */
    private WeakMap $returnValueGeneration;

    /**
     * @var WeakMap<object, InvocationHandler>
     */
    private WeakMap $invocationMocker;

    public static function getInstance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    private function __construct()
    {
        $this->configurableMethods   = [];
        $this->returnValueGeneration = new WeakMap;
        $this->invocationMocker      = new WeakMap;
    }

    /**
     * @param class-string             $class
     * @param list<ConfigurableMethod> $configurableMethods
     */
    public function initConfigurableMethods(string $class, array $configurableMethods): void
    {
        $this->configurableMethods[$class] = $configurableMethods;
    }

    public function setReturnValueGeneration(object $mock, bool $returnValueGeneration): void
    {
        $this->returnValueGeneration[$mock] = $returnValueGeneration;
    }

    /**
     * @param class-string $class
     */
    public function getInvocationHandler(string $class, object $mock): InvocationHandler
    {
        if (!isset($this->invocationMocker[$mock])) {
            $this->invocationMocker[$mock] = new InvocationHandler(
                $this->configurableMethods[$class] ?? [],
                $this->returnValueGeneration[$mock] ?? true,
            );
        }

        return $this->invocationMocker[$mock];
    }

    public function unsetInvocationHandler(object $mock): void
    {
        unset($this->invocationMocker[$mock]);
    }

    public function cloneInvocationHandler(object $mock): void
    {
        if (isset($this->invocationMocker[$mock])) {
            $this->invocationMocker[$mock] = clone $this->invocationMocker[$mock];
        }
    }
}
