<?php
require_once 'PHPUnit/Framework.php';

class PHPUnit_Extensions_XHProfListener implements PHPUnit_Framework_TestListener
{
    private $runs = array();
    private $options = array();
    private $suites = 0;

    public function __construct(array $options = array())
    {
        if (!extension_loaded('xhprof')) {
            throw new Exception("XHProf Extension is required to have this Listener work.");
        }
        if (!isset($options['appNamespace'])) {
            throw new Exception("XHProf Listener Option 'appNamespace' is missing.");
        }
        if (!isset($options['xhprofLibFile']) || !file_exists($options['xhprofLibFile'])) {
            throw new Exception("XHProf Listener Option 'xhprofLibFile' is missing or does not exist.");
        }
        if (!isset($options['xhprofRunsFile']) || !file_exists($options['xhprofRunsFile'])) {
            throw new Exception("XHProf Listener Option 'xhprofRunsFile' is missing or does not exist.");
        }

        require_once $options['xhprofLibFile'];
        require_once $options['xhprofRunsFile'];
        $this->options = $options;
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {

    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {

    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        if (!isset($this->options['xhprofFlags'])) {
            $flags = XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY;
        } else {
            $flags = 0;
            foreach (explode(',', $this->options['xhprofFlags']) AS $flag) {
                $flags += constant($flag);
            }
        }

        xhprof_enable($flags);
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $profiler_namespace = $this->options['appNamespace'];
        $xhprof_data = xhprof_disable();
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

        // url to the XHProf UI libraries (change the host name and path)
        $this->runs[] = $this->options['xhprofWeb'] . '?run=' . $run_id . '&source=' . $profiler_namespace;
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites++;
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->suites--;
        if ($this->suites == 0){
            echo "XHPRof Runs: " .count($this->runs) . "\n";
            foreach ($this->runs AS $run) {
                echo " * " . $run . "\n";
            }
            echo "\n";
        }
    }
}
