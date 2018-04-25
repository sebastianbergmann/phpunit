<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Matcher;

use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * Invocation matcher which allows any parameters to a method.
 */
class AnyParameters extends StatelessInvocation
{
    /**
     * @return string
     */
    public function toString(): string
    {
        return 'with any parameters';
    }

    /**
     * @param BaseInvocation $invocation
     *
     * @return bool
     */
    public function matches(BaseInvocation $invocation)
    {
        return true;
    }
}
