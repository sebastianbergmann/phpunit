<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 4.5.0
 */

/**
 * Class that takes a TestSuite and randomize the order of the tests inside.
 *
 * @package    PHPUnit
 * @subpackage Extensions
 * @author     Jose Armesto <jose@armesto.net>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 4.5.0
 */
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