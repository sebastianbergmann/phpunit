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

class TestListenerTest extends TestCase implements TestListener
{
    protected $endCount;
    protected $errorCount;
    protected $failureCount;
    protected $warningCount;
    protected $notImplementedCount;
    protected $riskyCount;
    protected $skippedCount;
    protected $result;
    protected $startCount;

    protected function setUp(): void
    {
        $this->result = new TestResult;
        $this->result->addListener($this);

        $this->endCount            = 0;
        $this->failureCount        = 0;
        $this->notImplementedCount = 0;
        $this->riskyCount          = 0;
        $this->skippedCount        = 0;
        $this->startCount          = 0;
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->errorCount++;
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->warningCount++;
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->failureCount++;
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        $this->notImplementedCount++;
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        $this->riskyCount++;
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        $this->skippedCount++;
    }

    public function startTestSuite(TestSuite $suite): void
    {
    }

    public function endTestSuite(TestSuite $suite): void
    {
    }

    public function startTest(Test $test): void
    {
        $this->startCount++;
    }

    public function endTest(Test $test, float $time): void
    {
        $this->endCount++;
    }

    public function testError(): void
    {
        $test = new \TestError;
        $test->run($this->result);

        $this->assertEquals(1, $this->errorCount);
        $this->assertEquals(1, $this->endCount);
    }

    public function testFailure(): void
    {
        $test = new \Failure;
        $test->run($this->result);

        $this->assertEquals(1, $this->failureCount);
        $this->assertEquals(1, $this->endCount);
    }

    public function testStartStop(): void
    {
        $test = new \Success;
        $test->run($this->result);

        $this->assertEquals(1, $this->startCount);
        $this->assertEquals(1, $this->endCount);
    }
}
