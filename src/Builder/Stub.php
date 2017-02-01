<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\MockObject\Builder;

/**
 * Builder interface for stubs which are actions replacing an invocation.
 *
 * @since Interface available since Release 1.0.0
 */
interface Stub extends Identity
{
    /**
     * Stubs the matching method with the stub object $stub. Any invocations of
     * the matched method will now be handled by the stub instead.
     *
     * @param \PHPUnit\Framework\MockObject\Stub $stub
     *
     * @return Identity
     */
    public function will(\PHPUnit\Framework\MockObject\Stub $stub);
}
