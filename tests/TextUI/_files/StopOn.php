<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
final class StopOn extends PHPUnit\Framework\TestCase
{
    public function testShouldFail()
    {
        $this->fail('Always fail');
    }

    public function testShouldBeRisky()
    {
        // Always risky, no assertion
    }

    public function testShouldBeIncomplete()
    {
        $this->markTestIncomplete('Always incomplete');
    }

    public function testShouldBeSkipped()
    {
        $this->markTestSkipped('Always skip');
    }

    public function testShouldBeWarning()
    {
        \trigger_error('Should error', \E_USER_WARNING);
    }

    public function testShouldBeError()
    {
        \trigger_error('Should error', \E_USER_NOTICE);
    }

    public function testNeverExecutedInFailRiskyIncompleteSkippedError()
    {
        // should be the last test for --stop-on-* flags to exclude
        $this->fail('--stop-on-* should not execute this test');
    }
}
