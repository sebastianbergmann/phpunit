<?php

class PHPUnit_Extensions_RandomTestSuite
{
	/**
     * Order the TestSuite tests in a random order.
     * 
     * @param  \PHPUnit_Framework_Test  $suite     The suite to randomize.
     * @param  array                    $arguments Arguments to use.
     * @return \PHPUnit_Framework_Test
     */
	public function randomizeTestSuite(\PHPUnit_Framework_Test $suite, $seed)
	{
        if ($this->testSuiteContainsOtherSuites($suite))
        {
            $this->randomizeSuiteThatContainsOtherSuites($suite, $seed);
        }
        else
        {
            $this->randomizeSuite($suite, $seed);
        }

        return $suite;
	}

	/**
	 * Randomize each Test Suite inside the main Test Suite.
	 * 
	 * @param  \PHPUnit_Framework_Test $suite Main Test Suite to randomize.
	 * @param  integer                 $seed  Seed to use.
	 * @return \PHPUnit_Framework_Test
	 */
	private function randomizeSuiteThatContainsOtherSuites($suite, $seed)
    {
        $order = 0;
        foreach ($suite->tests() as $test) {
            $this->randomizeSuite($test, $seed, $order);
            $order++;
        }

        return $this->randomizeSuite($suite, $seed, $order);
    }

    /**
     * Test Suites can contain other Test Suites or just Test Cases.
     * 
     * @param  \PHPUnit_Framework_Test $suite
     * @return Boolean
     */
    private function testSuiteContainsOtherSuites($suite)
    {
        $tests = $suite->tests();
        return isset($tests[0]) && $tests[0] instanceof \PHPUnit_Framework_TestSuite;
    }

    /**
     * Randomize the test cases inside a TestSuite, with the given seed.
     * 
     * @param  \PHPUnit_Framework_Test 	$suite Test suite to randomize.
     * @param  integer 					$seed  Seed to be used for the random funtion.
     * @param  integer 					$order Arbitrary value to "salt" the seed.
     * @return \PHPUnit_Framework_Test
     */
    private function randomizeSuite($suite, $seed, $order = 0)
    {
        $reflected = new \ReflectionObject($suite);
        $property = $reflected->getProperty('tests');
        $property->setAccessible(true);
        $property->setValue($suite, $this->randomizeTestsCases($suite->tests(), $seed, $order));

        return $suite;
    }

    /**
     * Randomize an array of TestCases.
     *
     * @param  array 	$tests  TestCases to randomize.
     * @param  integer  $seed   Seed used for PHP to randomize the array.
     * @param  integer  $order  A salt so it doesn't randomize all the classes in the same "random" order.
     * @return array            Randomized array
     */
    private function randomizeTestsCases(array $tests, $seed, $order)
    {
        srand($seed + $order);
        shuffle($tests);
        return $tests;
    }
}