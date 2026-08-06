<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\MockObject;

use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

final class InvocationOrderThatNeverMatches extends InvocationOrder
{
    public function toString(): string
    {
        return 'never matches';
    }

    public function verify(): void
    {
    }

    public function matches(Invocation $invocation): bool
    {
        return false;
    }
}
