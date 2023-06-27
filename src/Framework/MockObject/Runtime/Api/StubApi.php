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

/**
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait StubApi
{
    /** @noinspection MagicMethodsValidityInspection */
    public static function __phpunit_initConfigurableMethods(ConfigurableMethod ...$configurableMethods): void
    {
        InternalStubState::getInstance()->initConfigurableMethods(self::class, $configurableMethods);
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration): void
    {
        InternalStubState::getInstance()->setReturnValueGeneration($this, $returnValueGeneration);
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_getInvocationHandler(): InvocationHandler
    {
        return InternalStubState::getInstance()->getInvocationHandler(self::class, $this);
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_unsetInvocationMocker(): void
    {
        InternalStubState::getInstance()->unsetInvocationHandler($this);
    }
}
