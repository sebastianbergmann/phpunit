<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
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
// $Id: RepeatedTest.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Extensions/TestDecorator.php';

/**
 * A Decorator that runs a test repeatedly.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 */
class PHPUnit2_Extensions_RepeatedTest extends PHPUnit2_Extensions_TestDecorator {
    // {{{ Members

    /**
    * @var    integer
    * @access private
    */
    private $timesRepeat = 1;

    // }}}
    // {{{ public function __construct(PHPUnit2_Framework_Test $test, $timesRepeat = 1)

    /**
    * Constructor.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @param  integer                 $timesRepeat
    * @access public
    */
    public function __construct(PHPUnit2_Framework_Test $test, $timesRepeat = 1) {
        parent::__construct($test);

        if (is_integer($timesRepeat) &&
            $timesRepeat >= 0) {
            $this->timesRepeat = $timesRepeat;
        } else {
            throw new Exception('Illegal argument.');
        }
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
        return $this->timesRepeat * $this->test->countTestCases();
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

        for ($i = 0; $i < $this->timesRepeat && !$result->shouldStop(); $i++) {
            $this->test->run($result);
        }

        return $result;
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
