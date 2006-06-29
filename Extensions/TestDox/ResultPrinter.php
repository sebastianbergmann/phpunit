<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit                                                        |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: ResultPrinter.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Extensions/TestDox/NamePrettifier.php';
require_once 'PHPUnit2/Util/Printer.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 */
abstract class PHPUnit2_Extensions_TestDox_ResultPrinter extends PHPUnit2_Util_Printer implements PHPUnit2_Framework_TestListener {
    // {{{ Instance Variables

    /**
    * @var    PHPUnit2_Extensions_TestDox_NamePrettifier
    * @access protected
    */
    protected $prettifier;

    /**
    * @var    string
    * @access protected
    */
    protected $testClass = '';

    /**
    * @var    boolean
    * @access protected
    */
    protected $testFailed = FALSE;

    // }}}
    // {{{ public function __construct($out = NULL)

    /**
    * Constructor.
    *
    * @param  resource  $out
    * @access public
    */
    public function __construct($out = NULL) {
        parent::__construct($out);

        $this->prettifier = new PHPUnit2_Extensions_TestDox_NamePrettifier;
        $this->startRun();
    }

    // }}}
    // {{{ public function __destruct()

    /**
    * Destructor.
    *
    * @access public
    */
    public function __destruct() {
        $this->endClass($this->prettifier->prettifyTestClass($this->testClass));
        $this->endRun();

        parent::__destruct();
    }

    // }}}
    // {{{ public function factory($type, $out)

    /**
    * Abstract Factory.
    *
    * @param  string    $type
    * @param  resource  $out
    * @access public
    * @static
    */
    public static function factory($type, $out = NULL) {
        $class = 'PHPUnit2_Extensions_TestDox_ResultPrinter_' . $type;

        if (@require_once('PHPUnit2/Extensions/TestDox/ResultPrinter/' . $type . '.php')) {
            $object = new $class($out);

            return $object;
        } else {
            throw new Exception(
              sprintf(
                'Could not load class %s.',
                $class
              )
            );
        }
    }

    // }}}
    // {{{ public function addError(PHPUnit2_Framework_Test $test, Exception $e)

    /**
    * An error occurred.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @param  Exception               $e
    * @access public
    */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed = TRUE;
    }

    // }}}
    // {{{ public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

    /**
    * A failure occurred.
    *
    * @param  PHPUnit2_Framework_Test                 $test
    * @param  PHPUnit2_Framework_AssertionFailedError $e
    * @access public
    */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->testFailed = TRUE;
    }

    // }}}
    // {{{ public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)

    /**
    * Incomplete test.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @param  Exception               $e
    * @access public
    */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed = TRUE;
    }

    // }}}
    // {{{ public function startTestSuite(PHPUnit2_Framework_TestSuite $suite)

    /**
    * A testsuite started.
    *
    * @param  PHPUnit2_Framework_TestSuite $suite
    * @access public
    * @since  2.2.0
    */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    // }}}
    // {{{ public function endTestSuite(PHPUnit2_Framework_TestSuite $suite)

    /**
    * A testsuite ended.
    *
    * @param  PHPUnit2_Framework_TestSuite $suite
    * @access public
    * @since  2.2.0
    */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    // }}}
    // {{{ public function startTest(PHPUnit2_Framework_Test $test)

    /**
    * A test started.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function startTest(PHPUnit2_Framework_Test $test) {
        $class = get_class($test);

        if ($this->testClass != $class) {
            if ($this->testClass != '') {
                $this->endClass($this->prettifier->prettifyTestClass($this->testClass));
            }

            $this->startClass($this->prettifier->prettifyTestClass($class));
            $this->testClass = $class;
        }

        $this->testFailed = FALSE;
    }

    // }}}
    // {{{ public function endTest(PHPUnit2_Framework_Test $test)

    /**
    * A test ended.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function endTest(PHPUnit2_Framework_Test $test) {
        if (!$this->testFailed) {
            $this->onTest($this->prettifier->prettifyTestMethod($test->getName()));
        }
    }

    // }}}
    // {{{ abstract protected function startClass($name)

    /**
    * Handler for 'start class' event.
    *
    * @param  string $name
    * @access public
    * @abstract
    */
    abstract protected function startClass($name);

    // }}}
    // {{{ abstract protected function onTest($name)

    /**
    * Handler for 'on test' event.
    *
    * @param  string $name
    * @access public
    * @abstract
    */
    abstract protected function onTest($name);

    // }}}
    // {{{ abstract protected function endClass($name)

    /**
    * Handler for 'end class' event.
    *
    * @param  string $name
    * @access public
    * @abstract
    */
    abstract protected function endClass($name);

    // }}}
    // {{{ abstract protected function startRun()

    /**
    * Handler for 'start run' event.
    *
    * @access public
    * @abstract
    */
    abstract protected function startRun();

    // }}}
    // {{{ abstract protected function endRun()

    /**
    * Handler for 'end run' event.
    *
    * @access public
    * @abstract
    */
    abstract protected function endRun();

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
