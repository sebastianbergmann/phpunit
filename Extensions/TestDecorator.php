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
// $Id: TestDecorator.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestResult.php';

/**
 * A Decorator for Tests.
 *
 * Use TestDecorator as the base class for defining new
 * test decorators. Test decorator subclasses can be introduced
 * to add behaviour before or after a test is run.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 */
class PHPUnit2_Extensions_TestDecorator extends PHPUnit2_Framework_Assert implements PHPUnit2_Framework_Test {
    // {{{ Members

    /**
    * The Test to be decorated.
    *
    * @var    object
    * @access protected
    */
    protected $test = NULL;

    // }}}
    // {{{ public function __construct(PHPUnit2_Framework_Test $test)

    /**
    * Constructor.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function __construct(PHPUnit2_Framework_Test $test) {
        $this->test = $test;
    }

    // }}}
    // {{{ public function toString()

    /**
    * Returns a string representation of the test.
    *
    * @return string
    * @access public
    */
    public function toString() {
        return $this->test->toString();
    }

    // }}}
    // {{{ public function basicRun(PHPUnit2_Framework_TestResult $result)

    /**
    * Runs the test and collects the
    * result in a TestResult.
    *
    * @param  PHPUnit2_Framework_TestResult $result
    * @access public
    */
    public function basicRun(PHPUnit2_Framework_TestResult $result) {
        $this->test->run($result);
    }

    // }}}
    // {{{ public function countTestCases()

    /**
    * Counts the number of test cases that
    * will be run by this test.
    *
    * @return integer
    * @access public
    */
    public function countTestCases() {
        return $this->test->countTestCases();
    }

    // }}}
    // {{{ protected function createResult()

    /**
    * Creates a default TestResult object.
    *
    * @return PHPUnit2_Framework_TestResult
    * @access protected
    */
    protected function createResult() {
        return new PHPUnit2_Framework_TestResult;
    }

    // }}}
    // {{{ public function getTest()

    /**
    * Returns the test to be run.
    *
    * @return PHPUnit2_Framework_Test
    * @access public
    */
    public function getTest() {
        return $this->test;
    }

    // }}}
    // {{{ public function run($result = NULL)

    /**
    * Runs the decorated test and collects the
    * result in a TestResult.
    *
    * @param  PHPUnit2_Framework_TestResult $result
    * @return PHPUnit2_Framework_TestResult
    * @access public
    */
    public function run($result = NULL) {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        // XXX: Workaround for missing ability to declare type-hinted parameters as optional.
        else if (!($result instanceof PHPUnit2_Framework_TestResult)) {
            throw new Exception(
              'Argument 1 must be an instance of PHPUnit2_Framework_TestResult.'
            );
        }

        $this->basicRun($result);

        return $result;
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
