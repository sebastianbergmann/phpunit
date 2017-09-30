<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\MockObject\Matcher\Invocation;

/**
 * Stubs a method by returning a user-defined value.
 */
interface MatcherCollection
{
    /**
     * Adds a new matcher to the collection which can be used as an expectation
     * or a stub.
     *
     * @param Invocation $matcher Matcher for invocations to mock objects
     */
    public function addMatcher(Invocation $matcher);
}
