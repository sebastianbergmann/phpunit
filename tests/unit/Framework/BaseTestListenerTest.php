<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

class BaseTestListenerTest extends TestCase
{
    /**
     * @var TestResult
     */
    private $result;

    public function testEndEventsAreCounted()
    {
        $this->result = new TestResult;
        $listener     = new \BaseTestListenerSample;

        $this->result->addListener($listener);

        $test = new \Success;
        $test->run($this->result);

        $this->assertEquals(1, $listener->endCount);
    }
}
