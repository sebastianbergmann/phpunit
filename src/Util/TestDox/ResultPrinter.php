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
     * Constructor.
     *
     * @param resource $out
     */
    public function __construct($out = null)
    {
        parent::__construct($out);

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
     * @since  Method available since Release 4.0.0
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
     * @since  Method available since Release 3.0.0
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
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A testsuite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     *
     * @since  Method available since Release 2.2.0
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
            $this->currentTestMethodPrettified = $annotations['method']['testdox'][0];
        } else {
            $this->currentTestMethodPrettified = $this->prettifier->prettifyTestMethod($test->getName(false));
        }

        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
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

        if (!isset($this->tests[$this->currentTestMethodPrettified])) {
            if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $this->tests[$this->currentTestMethodPrettified]['success'] = 1;
                $this->tests[$this->currentTestMethodPrettified]['failure'] = 0;
            } else {
                $this->tests[$this->currentTestMethodPrettified]['success'] = 0;
                $this->tests[$this->currentTestMethodPrettified]['failure'] = 1;
            }
        } else {
            if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
                $this->tests[$this->currentTestMethodPrettified]['success']++;
            } else {
                $this->tests[$this->currentTestMethodPrettified]['failure']++;
            }
        }

        $this->currentTestClassPrettified  = null;
        $this->currentTestMethodPrettified = null;
    }

    /**
     * @since  Method available since Release 2.3.0
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

    private function isOfInterest(PHPUnit_Framework_Test $test)
    {
        return $test instanceof PHPUnit_Framework_TestCase && get_class($test) != 'PHPUnit_Framework_WarningTestCase';
    }
}
