<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Matcher;

use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\MockObject\Verifiable;
use PHPUnit\Framework\SelfDescribing;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface Invocation extends SelfDescribing, Verifiable
{
    /**
     * Registers the invocation $invocation in the object as being invoked.
     * This will only occur after matches() returns true which means the
     * current invocation is the correct one.
     *
     * The matcher can store information from the invocation which can later
     * be checked in verify(), or it can check the values directly and throw
     * and exception if an expectation is not met.
     *
     * If the matcher is a stub it will also have a return value.
     *
     * @param BaseInvocation $invocation Object containing information on a mocked or stubbed method which was invoked
     */
    public function invoked(BaseInvocation $invocation);

    /**
     * Checks if the invocation $invocation matches the current rules. If it does
     * the matcher will get the invoked() method called which should check if an
     * expectation is met.
     *
     * @param BaseInvocation $invocation Object containing information on a mocked or stubbed method which was invoked
     *
     * @return bool
     */
    public function matches(BaseInvocation $invocation);
}
