<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @since      Class available since Release 2.0.0
 * @covers     PHPUnit_Extensions_RepeatedTest
 */
class Extensions_RepeatedTestTest extends PHPUnit_Framework_TestCase
{
    protected $suite;

    public function __construct()
    {
        $this->suite = new PHPUnit_Framework_TestSuite;

        $this->suite->addTest(new Success);
        $this->suite->addTest(new Success);
        $this->suite->addTest(new Failure);
    }

    public function testRepeatedOnce()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 1);
        $this->assertEquals(3, count($test));

        $result = $test->run();
        $this->assertEquals(3, count($result));
    }

    public function testRepeatedMoreThanOnce()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 3);
        $this->assertEquals(9, count($test));

        $result = $test->run();
        $this->assertEquals(9, count($result));
    }

    public function testRepeatedMoreThanOnceOnlyFailed()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 3, false, true);
        // Intends to run 9 tests.
        $this->assertEquals(9, count($test));

        $result = $test->run();
        // But eventually skips 4 because the Success test already succeeded
        // the first time it ran, and we chose to only repeat the failed tests.
        $this->assertEquals(4, $result->skippedCount());
    }

    public function testRepeatedDataProviderMoreThanOnceOnlyFailed()
    {
        $test = new PHPUnit_Extensions_RepeatedTest(new DataProviderTest, 3, false, true);
        // Intends to run 3 times 1 test with 1 test method with 4 data sets
        // provided. 2 sets cause an error. 2 sets cause a success.
        $this->assertEquals(3, count($test));

        $result = $test->run();
        // Test case didn't get skipped because it kept failing.
        $this->assertEquals(0, $result->skippedCount());
    }

    public function testRepeatedTestCaseSuccessMoreThanOnceOnlyFailed()
    {
        $test = new PHPUnit_Extensions_RepeatedTest(new Success, 3, false, true);
        $this->assertEquals(3, count($test));

        $result = $test->run();
        $this->assertEquals(2, $result->skippedCount());
    }

    public function testRepeatedTestCaseFailureMoreThanOnceOnlyFailed()
    {
        $test = new PHPUnit_Extensions_RepeatedTest(new Failure, 3, false, true);
        $this->assertEquals(3, count($test));

        $result = $test->run();
        // Test case didn't get skipped because it kept failing.
        $this->assertEquals(0, $result->skippedCount());
    }

    public function testRepeatedZero()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 0);
        $this->assertEquals(0, count($test));

        $result = $test->run();
        $this->assertEquals(0, count($result));
    }

    public function testRepeatedNegative()
    {
        try {
            $test = new PHPUnit_Extensions_RepeatedTest($this->suite, -1);
        } catch (Exception $e) {
            return;
        }

        $this->fail('Should throw an Exception');
    }
}
