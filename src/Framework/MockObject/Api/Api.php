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

use PHPUnit\Framework\MockObject\Builder\InvocationMocker as InvocationMockerBuilder;
use PHPUnit\Framework\MockObject\Matcher\Invocation;

/**
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait Api
{
    /**
     * @var ConfigurableMethod[]
     */
    private static $__phpunit_configurableMethods;

    /**
     * @var @object
     */
    private $__phpunit_originalObject;

    /**
     * @var @bool
     */
    private $__phpunit_returnValueGeneration = true;

    /**
     * @var InvocationMocker
     */
    private $__phpunit_invocationMocker;

    /** @noinspection MagicMethodsValidityInspection */
    public static function __phpunit_initConfigurableMethods(ConfigurableMethod ...$configurableMethods): void
    {
        if (isset(static::$__phpunit_configurableMethods)) {
            throw new ConfigurableMethodsAlreadyInitializedException(
                'Configurable methods is already initialized and can not be reinitialized'
            );
        }

        static::$__phpunit_configurableMethods = $configurableMethods;
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_setOriginalObject($originalObject): void
    {
        $this->__phpunit_originalObject = $originalObject;
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration): void
    {
        $this->__phpunit_returnValueGeneration = $returnValueGeneration;
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_getInvocationMocker(): InvocationMocker
    {
        if ($this->__phpunit_invocationMocker === null) {
            $this->__phpunit_invocationMocker = new InvocationMocker(
                static::$__phpunit_configurableMethods,
                $this->__phpunit_returnValueGeneration
            );
        }

        return $this->__phpunit_invocationMocker;
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_hasMatchers(): bool
    {
        return $this->__phpunit_getInvocationMocker()->hasMatchers();
    }

    /** @noinspection MagicMethodsValidityInspection */
    public function __phpunit_verify(bool $unsetInvocationMocker = true): void
    {
        $this->__phpunit_getInvocationMocker()->verify();

        if ($unsetInvocationMocker) {
            $this->__phpunit_invocationMocker = null;
        }
    }

    public function expects(Invocation $matcher): InvocationMockerBuilder
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }
}
