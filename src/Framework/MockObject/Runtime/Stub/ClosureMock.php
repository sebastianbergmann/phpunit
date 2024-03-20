<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MethodCannotBeConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameAlreadyConfiguredException;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

/**
 * @mixin \PHPUnit\Framework\MockObject\MockObject
 *
 * @method InvocationMocker method($constraint)
 */
class ClosureMock
{
    public function __invoke(): mixed
    {
        return null;
    }

    /**
     * @throws InvalidArgumentException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     */
    public function expectsClosure(InvocationOrder $invocationRule): InvocationMocker
    {
        return $this->expects($invocationRule)
            ->method('__invoke');
    }

    public function closure(): InvocationMocker
    {
        return $this->method('__invoke');
    }
}
