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
    private readonly StubInternalState $__phpunit_stubInternalState;

    public function __phpunit_initStubInternalState(): void
    {
        $this->__phpunit_stubInternalState = new StubInternalState;
    }

    /** @noinspection MagicMethodsValidityInspection */
    public static function __phpunit_initConfigurableMethods(ConfigurableMethod ...$configurableMethods): void
    {
        StubInternalState::initConfigurableMethods(self::class, $configurableMethods);
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration): void
    {
        $this->__phpunit_stubInternalState->setReturnValueGeneration($returnValueGeneration);
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_getInvocationHandler(): InvocationHandler
    {
        return $this->__phpunit_stubInternalState->getInvocationHandler(self::class);
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_unsetInvocationMocker(): void
    {
        $this->__phpunit_stubInternalState->unsetInvocationHandler();
    }
}
