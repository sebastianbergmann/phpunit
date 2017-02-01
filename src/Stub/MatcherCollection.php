<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stubs a method by returning a user-defined value.
 */
interface PHPUnit_Framework_MockObject_Stub_MatcherCollection
{
    /**
     * Adds a new matcher to the collection which can be used as an expectation
     * or a stub.
     *
     * @param PHPUnit_Framework_MockObject_Matcher_Invocation $matcher Matcher for invocations to mock objects
     */
    public function addMatcher(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher);
}
