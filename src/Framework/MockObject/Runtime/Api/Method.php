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

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait Method
{
    abstract public function __phpunit_getInvocationHandler(): InvocationHandler;

    public function method(Constraint|PropertyHook|string $constraint): InvocationStubber
    {
        return $this
            ->__phpunit_getInvocationHandler()
            ->expects(new AnyInvokedCount)
            ->method($constraint);
    }
}
