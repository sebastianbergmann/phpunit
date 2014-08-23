<?php

class PHPUnit_Extensions_RandomTestSuitePrinter extends \PHPUnit_TextUI_ResultPrinter
{
    /**
     * Seed used to randomize the order of the tests.
     *
     * @var integer
     */
    protected $seed;

    /**
     * Constructor.
     *
     * @param  mixed                       $out
     * @param  boolean                     $verbose
     * @param  boolean                     $colors
     * @param  boolean                     $debug
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.0.0
     */
    public function __construct($out = null, $verbose = false, $colors = false, $debug = false, $seed = null)
    {
        parent::__construct($out, $verbose, $colors, $debug);
        $this->seed = $seed;
    }

    /**
     * Just add to the output the seed used to randomize the test suite.
     * 
     * @param  PHPUnit_Framework_TestResult $result
     */
    protected function printFooter(\PHPUnit_Framework_TestResult $result)
    {
        parent::printFooter($result);

        $this->writeNewLine();
        $this->write("Randomized with seed: {$this->seed}");
        $this->writeNewLine();
    }
} 