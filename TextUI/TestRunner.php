<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestRunner.php 539 2006-02-13 16:08:42Z sb $
//

if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'PHPUnit2_TextUI_TestRunner::main');
}

require_once 'Console/Getopt.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Runner/BaseTestRunner.php';
require_once 'PHPUnit2/Runner/Version.php';
require_once 'PHPUnit2/TextUI/ResultPrinter.php';
@include_once 'PHPUnit2/TestDox/ResultPrinter.php';
@include_once 'Benchmark/Timer.php';

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  TextUI
 */
class PHPUnit2_TextUI_TestRunner extends PHPUnit2_Runner_BaseTestRunner {
    // {{{ Constants

    const SUCCESS_EXIT   = 0;
    const FAILURE_EXIT   = 1;
    const EXCEPTION_EXIT = 2;

    // }}}
    // {{{ Members

    /**
    * @var    PHPUnit2_TextUI_ResultPrinter
    * @access private
    */
    private $printer = null;

    // }}}
    // {{{ public static function main()

    /**
    * @access public
    * @static
    */
    public static function main() {
        $aTestRunner = new PHPUnit2_TextUI_TestRunner;

        try {
            $result = $aTestRunner->start($_SERVER['argv']);

            if (!$result->wasSuccessful()) {
                exit(self::FAILURE_EXIT);
            }

            exit(self::SUCCESS_EXIT);
        }

        catch (Exception $e) {
            print $e->getMessage();
            exit(self::EXCEPTION_EXIT);
        }
    }

    // }}}
    // {{{ protected function start($arguments)

    /**
    * @param  array $arguments
    * @access protected
    */
    protected function start($arguments) {
        $wait = false;

        $possibleOptions = array(
          'help',
          'testdox',
          'version',
          'wait'
        );

        $options = Console_Getopt::getopt(
          $arguments,
          '',
          $possibleOptions
        );

        foreach ($options[0] as $option) {
            switch ($option[0]) {
                case '--help': {
                    $this->showHelp();
                    exit(self::SUCCESS_EXIT);
                }
                break;

                case '--testdox': {
                    if (class_exists('PHPUnit2_TestDox_ResultPrinter')) {
                        $this->printer = new PHPUnit2_TestDox_ResultPrinter;
                    } else {
                        print "The PHPUnit2_TestDox package is needed for the '--testdox' option.\n";
                        exit(self::FAILURE_EXIT);
                    }
                }
                break;

                case '--version': {
                    print PHPUnit2_Runner_Version::getVersionString() . "\n";
                    exit(self::SUCCESS_EXIT);
                }
                break;

                case '--wait': {
                    $wait = true;
                }
                break;
            }
        }

        $test = isset($options[1][0]) ? $options[1][0] : false;

        if ($test === false) {
            $this->showHelp();

            exit(self::SUCCESS_EXIT);
        }

        try {
			      return $this->doRun(
			        $this->getTest($test),
			        $wait
			      );
        }

        catch (Exception $e) {
            throw new Exception(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }
    }

    // }}}
    // {{{ public static function run($test)

    /**
    * @param  mixed $test
    * @access public
    * @static
    */
  	public static function run($test) {
        if ($test instanceof ReflectionClass) {
  		      self::run(new PHPUnit2_Framework_TestSuite($testClass));
  		  }

        else if ($test instanceof PHPUnit2_Framework_Test) {
            $aTestRunner = new PHPUnit2_TextUI_TestRunner;

            return $aTestRunner->doRun($test);
        }
  	}

    // }}}
    // {{{ public static function runAndWait(PHPUnit2_Framework_Test $suite)

    /**
    * @param  PHPUnit2_Framework_Test $suite
    * @access public
    * @static
    */
  	public static function runAndWait(PHPUnit2_Framework_Test $suite) {
  		  $aTestRunner = new PHPUnit2_TextUI_TestRunner;
  		  $aTestRunner->doRun($suite, true);
  	}

    // }}}
    // {{{ protected TestResult createTestResult()

    /**
    * @return PHPUnit2_Framework_TestResult
    * @access protected
    */
  	protected function createTestResult() {
  		  return new PHPUnit2_Framework_TestResult;
  	}
	
    // }}}
    // {{{ public function doRun(PHPUnit2_Framework_Test $suite, $wait = false)

    /**
    * @param  PHPUnit2_Framework_Test $suite
    * @param  boolean                 $wait
    * @return PHPUnit2_Framework_TestResult
    * @access public
    */
  	public function doRun(PHPUnit2_Framework_Test $suite, $wait = false) {
    		$result = $this->createTestResult();

        if ($this->printer === null) {
            $this->printer = new PHPUnit2_TextUI_ResultPrinter;
        }

        $this->printer->write(
          PHPUnit2_Runner_Version::getVersionString() . "\n\n"
        );

    		$result->addListener($this->printer);

        if (class_exists('Benchmark_Timer')) {
            $timer = new Benchmark_Timer;
        }

        if (isset($timer)) {
            $timer->start();
        }

        $suite->run($result);

        if (isset($timer)) {
            $timer->stop();
            $timeElapsed = $timer->timeElapsed();
        } else {
            $timeElapsed = false;
        }

        $this->pause($wait);
        $this->printer->printResult($result, $timeElapsed);

    		return $result;
  	}

    // }}}
    // {{{ public function showHelp()

    /**
    * @access public
    */
    public function showHelp() {
        print PHPUnit2_Runner_Version::getVersionString() . "\n\n" .
              "Usage: phpunit [switches] UnitTest\n";

        if (class_exists('PHPUnit2_TestDox_ResultPrinter')) {
            "  --testdox          Uses the PHPUnit2_TestDox_ResultPrinter.\n";
        }

        print "  --wait             Waits for a keystroke after each test.\n" .
              "  --help             Prints this usage information.\n" .
              "  --version          Prints the version and exits.\n";
    }

    // }}}
    // {{{ protected function pause($wait)

    /**
    * @param  boolean $wait
    * @access protected
    */
    protected function pause($wait) {
        if (!$wait) {
            return;
        }

        $this->printer->printWaitPrompt();

        fgets(STDIN);
    }

    // }}}
    // {{{ public function testEnded($testName)

    /**
    * A test ended.
    *
    * @param  string  $testName
    * @access public
    */
    public function testEnded($testName) {
    }

    // }}}
    // {{{ public function testFailed($status, PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

    /**
    * A test failed.
    *
    * @param  integer                                 $status
    * @param  PHPUnit2_Framework_Test                 $test
    * @param  PHPUnit2_Framework_AssertionFailedError $e
    * @access public
    */
    public function testFailed($status, PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
    }

    // }}}
    // {{{ public function testStarted($testName)

    /**
    * A test started.
    *
    * @param  string  $testName
    * @access public
    */
    public function testStarted($testName) {
    }

    // }}}
    // {{{ protected function runFailed($message)

    /**
    * Override to define how to handle a failed loading of
    * a test suite.
    *
    * @param  string  $message
    * @access protected
    */
    protected function runFailed($message) {
        print $message;
        exit(self::FAILURE_EXIT);
    }

    // }}}
    // {{{ public function setPrinter(PHPUnit2_TextUI_ResultPrinter $resultPrinter)

    /**
    * @param  PHPUnit2_TextUI_ResultPrinter $resultPrinter
    * @access public
    */
    public function setPrinter(PHPUnit2_TextUI_ResultPrinter $resultPrinter) {
        $this->printer = $resultPrinter;
    }

    // }}}
}

if (PHPUnit2_MAIN_METHOD == 'PHPUnit2_TextUI_TestRunner::main') {
    PHPUnit2_TextUI_TestRunner::main();
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
