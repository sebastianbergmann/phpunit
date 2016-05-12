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
 * Base class for printers of TestDox documentation.
 *
 * @since Class available since Release 2.1.0
 */
abstract class PHPUnit_Util_TestDox_ResultPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var PHPUnit_Util_TestDox_NamePrettifier
     */
    protected $prettifier;

    /**
     * @var string
     */
    protected $testClass = '';

    /**
     * @var int
     */
    protected $testStatus = false;

     /**
     * @var string
     */
    protected $testError;

    /**
     * @var array
     */
    protected $tests = [];

    /**
     * @var int
     */
    protected $successful = 0;

    /**
     * @var int
     */
    protected $warned = 0;

    /**
     * @var int
     */
    protected $failed = 0;

    /**
     * @var int
     */
    protected $risky = 0;

    /**
     * @var int
     */
    protected $skipped = 0;

    /**
     * @var int
     */
    protected $incomplete = 0;

    /**
     * @var string
     */
    protected $currentTestClassPrettified;

    /**
     * @var string
     */
    protected $currentTestMethodPrettified;

     /**
     * @var boolean Verbose
     */
    protected $verbose;

    /**
     * @var array A lookup map to convert test status into a single character.
     */
    protected $testStatusIndicatorCharMap = [
            PHPUnit_Runner_BaseTestRunner::STATUS_PASSED     => 'X',
            PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED    => 'S',
            PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE => 'I',
            PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE    => 'F',
            PHPUnit_Runner_BaseTestRunner::STATUS_ERROR      => 'E',
            PHPUnit_Runner_BaseTestRunner::SUITE_METHODNAME  => '-'
        ];

    /**
     * @var array
     */
    private $groups;

    /**
     * @var array
     */
    private $excludeGroups;

    /**
     * @param resource $out
     * @param array    $groups
     * @param array    $excludeGroups
     */
    public function __construct($out = null, array $groups = [], array $excludeGroups = [], $verbose = false)
    {
        parent::__construct($out);

        $this->groups        = $groups;
        $this->excludeGroups = $excludeGroups;
        $this->verbose       = $verbose;

        $this->prettifier = new PHPUnit_Util_TestDox_NamePrettifier;
        $this->startRun();
    }

    /**
     * Flush buffer and close output.
     */
    public function flush()
    {
        $this->doEndClass();
        $this->endRun();

        parent::flush();
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testError = $e;
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
        $this->failed++;
    }

    /**
     * A warning occurred.
     *
     * @param PHPUnit_Framework_Test    $test
     * @param PHPUnit_Framework_Warning $e
     * @param float                     $time
     *
     * @since Method available since Release 5.1.0
     */
    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_WARNING;
        $this->warned++;
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testError = $e;
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
        $this->failed++;
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
        $this->incomplete++;
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_RISKY;
        $this->risky++;
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
        $this->skipped++;
    }

    /**
     * A testsuite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A testsuite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->testError = NULL;

        $class = get_class($test);

        if ($this->testClass != $class) {
            if ($this->testClass != '') {
                $this->doEndClass();
            }

            $classAnnotations = PHPUnit_Util_Test::parseTestMethodAnnotations($class);
            if (isset($classAnnotations['class']['testdox'][0])) {
                $this->currentTestClassPrettified = $classAnnotations['class']['testdox'][0];
            } else {
                $this->currentTestClassPrettified = $this->prettifier->prettifyTestClass($class);
            }

            $this->startClass($class);

            $this->testClass = $class;
            $this->tests     = [];
        }

        $annotations = $test->getAnnotations();

        if (isset($annotations['method']['testdox'][0])) {
            // MASTER: $this->currentTestMethodPrettified = $annotations['method']['testdox'][0];
            $tdArgumentSpec = $annotations['method']['dataProviderTestdox'][0];

            // generate sprintf format string
            $formatStr = NULL;
            if (is_numeric($tdArgumentSpec))
            {
                $formatStr = "%{$tdArgumentSpec}\$s";
            }
            else
            {
                $formatStr = $tdArgumentSpec;
            }

            // generate pretty test name
            $iterationArgs = $test->getData();
            $iterationTestName = trim(vsprintf($formatStr, $iterationArgs));
            $this->currentTestMethodPrettified .= ": {$iterationTestName}";
        } else {
            $this->currentTestMethodPrettified = $this->prettifier->prettifyTestMethod($test->getName(false));
        }

        if ($test instanceof PHPUnit_Framework_TestCase && $test->usesDataProvider()) {
            $this->currentTestMethodPrettified .= ' ' . $test->dataDescription();
        }

        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;

        // ensure name uniqueness
        if (isset($this->tests[$this->currentTestMethodPrettified]))
        {
            // try to append data set info
            $this->currentTestMethodPrettified .= $test->getDataSetAsString(FALSE);
        }
        if (isset($this->tests[$this->currentTestMethodPrettified])) throw new Exception("Test name already exists: {$this->currentTestMethodPrettified}");

        // initialize data set for this test+iteration
        $this->tests[$this->currentTestMethodPrettified] = array('success' => 0, 'failure' => 0, 'errors' => array());
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!$this->isOfInterest($test)) {
            return;
        }

        $this->tests[$this->currentTestMethodPrettified]['status'] = $this->testStatusIndicatorCharMap[$this->testStatus];
        if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
            $this->tests[$this->currentTestMethodPrettified]['success']++;
        } else {
            $this->tests[$this->currentTestMethodPrettified]['failure']++;
        }

        if ($this->testError)
        {
            $this->tests[$this->currentTestMethodPrettified]['errors'][] = $this->testError;
        }

        $this->onTest($this->currentTestMethodPrettified, $this->tests[$this->currentTestMethodPrettified]['failure'] == 0);

        $this->currentTestClassPrettified  = null;
        $this->currentTestMethodPrettified = null;
    }

    /**
     * @since Method available since Release 2.3.0
     */
    protected function doEndClass()
    {
        foreach ($this->tests as $name => $data) {
            $this->onTest($name, $data['failure'] == 0);
        }

        $this->endClass($this->testClass);
    }

    /**
     * Handler for 'start run' event.
     */
    protected function startRun()
    {
    }

    /**
     * Handler for 'start class' event.
     *
     * @param string $name
     */
    protected function startClass($name)
    {
    }

    /**
     * Handler for 'on test' event.
     *
     * @param string $name
     * @param bool   $success
     */
    protected function onTest($name, $success = true)
    {
    }

    /**
     * Handler for 'end class' event.
     *
     * @param string $name
     */
    protected function endClass($name)
    {
    }

    /**
     * Handler for 'end run' event.
     */
    protected function endRun()
    {
    }

    /**
     * @param PHPUnit_Framework_Test $test
     *
     * @return bool
     */
    private function isOfInterest(PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof PHPUnit_Framework_TestCase) {
            return false;
        }

        if ($test instanceof PHPUnit_Framework_WarningTestCase) {
            return false;
        }

        if (!empty($this->groups)) {
            foreach ($test->getGroups() as $group) {
                if (in_array($group, $this->groups)) {
                    return true;
                }
            }

            return false;
        }

        if (!empty($this->excludeGroups)) {
            foreach ($test->getGroups() as $group) {
                if (in_array($group, $this->excludeGroups)) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }
}
