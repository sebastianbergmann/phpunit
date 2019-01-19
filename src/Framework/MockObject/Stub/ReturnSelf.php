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

use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\Stub;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ReturnSelf implements Stub
{
    /**
     * @throws RuntimeException
     */
    public function invoke(Invocation $invocation)
    {
        if (!$invocation instanceof ObjectInvocation) {
            throw new RuntimeException(
                'The current object can only be returned when mocking an ' .
                'object, not a static class.'
            );
        }

        return $invocation->getObject();
    }

    public function toString(): string
    {
        return 'return the current object';
    }
}
