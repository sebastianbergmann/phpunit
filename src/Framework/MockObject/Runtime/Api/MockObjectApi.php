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

use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait MockObjectApi
{
    public function __phpunit_hasInvocationCountRule(): bool
    {
        return $this->__phpunit_getInvocationHandler()->hasInvocationCountRule();
    }

    public function __phpunit_hasParametersRule(): bool
    {
        return $this->__phpunit_getInvocationHandler()->hasParametersRule();
    }

    public function __phpunit_verify(bool $unsetInvocationMocker = true): void
    {
        $this->__phpunit_getInvocationHandler()->verify();

        if ($unsetInvocationMocker) {
            $this->__phpunit_unsetInvocationMocker();
        }
    }

    abstract public function __phpunit_state(): TestDoubleState;

    abstract public function __phpunit_getInvocationHandler(): InvocationHandler;

    abstract public function __phpunit_unsetInvocationMocker(): void;

    public function expects(InvocationOrder $matcher): InvocationStubber
    {
        return $this->__phpunit_getInvocationHandler()->expects($matcher);
    }
}
