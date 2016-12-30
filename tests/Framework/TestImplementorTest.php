<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestCase;

/**
 * @since      Class available since Release 2.0.0
 */
class Framework_TestImplementorTest extends TestCase
{
    /**
     * @covers TestCase
     */
    public function testSuccessfulRun()
    {
        $result = new TestResult;

        $test = new DoubleTestCase(new Success);
        $test->run($result);

        $this->assertEquals(count($test), count($result));
        $this->assertEquals(0, $result->errorCount());
        $this->assertEquals(0, $result->failureCount());
    }
}
