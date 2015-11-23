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
     * @var PHPUnit_Framework_TestCase[]
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
        if ($test instanceof PHPUnit_Framework_TestSuite) {
            $this->getAllTestCaseChildren($test);
        }
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
        for ($i = 1; $i <= $this->timesRepeat && !$result->shouldStop(); $i++) {
            if ($this->onlyRepeatFailed &&
                $i > 1 &&
                $this->test instanceof PHPUnit_Framework_TestCase &&
                $result->wasSuccessful()) {
                // The previous test case run succeeded.
                $this->skipTestCase($this->test, $result, $i);
                // Go to the next repeat at once, skipping the execution of the test.
                continue;
            }
            //@codingStandardsIgnoreEnd
            if ($this->test instanceof PHPUnit_Framework_TestSuite) {
                $this->test->setRunTestInSeparateProcess($this->processIsolation);

                if ($this->onlyRepeatFailed && $i > 1) {
                    $testsToRepeat = array();
                    foreach ($this->testCaseChildren as $test) {
                        $skipped = false;
                        foreach ($result->passed() as $key => $data) {
                            // The 'passed' array is keyed by test key.
                            // @see TestResult::endTest()
                            if ($key == get_class($test) . '::' . $test->getName()) {
                                // A previous test run succeeded.
                                $this->skipTestCase($test, $result, $i);
                                $skipped = true;
                                // Quit the foreach because we found a match.
                                break;
                            }
                        }
                        if (!$skipped) {
                            $testsToRepeat[] = $test;
                        }
                    }
                    $this->test->setTests($testsToRepeat);
                    // Don't filter tests any more, we just filtered them.
                    // Reset the filter.
                    $this->test->injectFilter(new PHPUnit_Runner_Filter_Factory());
                }
            }
            $this->basicRun($result);
        }

        return $result;
    }

    /**
     * Get all children test cases from a test suite.
     *
     * @param PHPUnit_Framework_TestSuite $test
     */
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
     * Skip the given test case once.
     *
     * @param PHPUnit_Framework_Test $test
     * @param PHPUnit_Framework_TestResult $result
     * @param int $ranCount
     *   How many times the test already ran or got skipped.
     */
    protected function skipTestCase($test, $result, $ranCount)
    {
        $message = sprintf('Test skipped in run number %d because it succeeded during a previous run.', $ranCount + 1);
        $result->addFailure($test, new PHPUnit_Framework_SkippedTestError($message), 0);
    }
}
