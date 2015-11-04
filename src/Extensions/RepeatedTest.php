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
 * A Decorator that runs a test repeatedly.
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Extensions_RepeatedTest extends PHPUnit_Extensions_TestDecorator
{
    /**
     * @var array
     */
    protected $testCaseChildren = array();

    /**
     * @var mixed
     */
    protected $filter = false;

    /**
     * @var array
     */
    protected $groups = array();

    /**
     * @var array
     */
    protected $excludeGroups = array();

    /**
     * @var boolean
     */
    protected $onlyRepeatFailed = false;

    /**
     * @var boolean
     */
    protected $processIsolation = false;

    /**
     * @var integer
     */
    protected $timesRepeat = 1;

    /**
     * @param  PHPUnit_Framework_Test      $test
     * @param  integer                     $timesRepeat
     * @param  boolean                     $processIsolation
     * @param  boolean                     $onlyRepeatFailed
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct(PHPUnit_Framework_Test $test, $timesRepeat = 1, $processIsolation = false, $onlyRepeatFailed = false)
    {
        parent::__construct($test);

        if (is_integer($timesRepeat) &&
            $timesRepeat >= 0) {
            $this->timesRepeat = $timesRepeat;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'positive integer'
            );
        }

        $this->processIsolation = $processIsolation;
        $this->onlyRepeatFailed = $onlyRepeatFailed;
    }

    /**
     * Counts the number of test cases that
     * will be run by this test.
     *
     * @return integer
     */
    public function count()
    {
        return $this->timesRepeat * count($this->test);
    }

    /**
     * Runs the decorated test and collects the
     * result in a TestResult.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @return PHPUnit_Framework_TestResult
     * @throws PHPUnit_Framework_Exception
     */
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }

        //@codingStandardsIgnoreStart
        for ($i = 0; $i < $this->timesRepeat && !$result->shouldStop(); $i++) {
            if ($this->onlyRepeatFailed &&
                $i > 0 &&
                $this->test instanceof PHPUnit_Framework_TestCase &&
                $result->wasSuccessful()) {
                // The previous test case run succeeded.
                $this->markNextRepeatedTestsAsSkipped($i, $result, $this->test);
                // Don't repeat it any more.
                break;
            }
            //@codingStandardsIgnoreEnd
            if ($this->test instanceof PHPUnit_Framework_TestSuite) {
                $this->test->setRunTestInSeparateProcess($this->processIsolation);

                if ($this->onlyRepeatFailed && $i > 0) {
                    $this->testCaseChildren = array();
                    $this->getAllTestCaseChildren($this->test);
                    $testsToRepeat = $this->getWhichTestsToRepeat($result, $i);
                    $this->test->setTests($testsToRepeat);
                    // Don't filter tests any more, we just filtered them.
                    // Reset the filter.
                    $this->test->injectFilter(new PHPUnit_Runner_Filter_Factory());
                }
            }
            $this->test->run($result);
        }

        return $result;
    }

    public function getAllTestCaseChildren(PHPUnit_Framework_TestSuite $test)
    {
        foreach ($test as $testChild) {
            if ($testChild instanceof PHPUnit_Framework_TestSuite) {
                // If the test itself is another test suite, then recurse.
                $this->getAllTestCaseChildren($testChild);
            } else {
                $this->testCaseChildren[] = $testChild;
            }
        }
    }

    /**
     * Get which tests failed, and thus should be repeated.
     *
     * @param PHPUnit_Framework_TestResult $result
     * @param int $ranCount
     *   How many times did the tests already run?
     * @return PHPUnit_Framework_Test[]
     */
    protected function getWhichTestsToRepeat($result, $ranCount)
    {
        $testsToRepeat = array();
        $unSuccessfulTests = array_merge($result->failures(), $result->errors());

        foreach ($this->testCaseChildren as $test) {
            $testWasSuccessful = true;

            foreach ($unSuccessfulTests as $failure) {
                /** @var $failure PHPUnit_Framework_TestFailure */
                if ($test === $failure->failedTest()) {
                    $testWasSuccessful = false;
                    $testsToRepeat[] = $test;
                    // Only repeat the test once. Don't repeat it as many times
                    // as it already failed.
                    break;
                }
            }
            if ($testWasSuccessful) {
                // The previous test case run succeeded.
                $this->markNextRepeatedTestsAsSkipped($ranCount, $result, $test);
            }
        }

        return $testsToRepeat;
    }

    /**
     * Mark the remaining repeated tests as skipped.
     *
     * @param int $ranCount
     *   How many times did the test already run?
     * @param PHPUnit_Framework_TestResult $result
     * @param PHPUnit_Framework_Test $test
     */
    protected function markNextRepeatedTestsAsSkipped($ranCount, $result, $test)
    {
        $skipped = new PHPUnit_Framework_SkippedTestError('Test already succeeded during previous run.');
        for ($j = 0; $j < $this->timesRepeat - $ranCount; $j++) {
            $result->addFailure($test, $skipped, 0);
        }
    }
}
