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
// $Id: PerformanceTestCase.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'Benchmark/Timer.php';
require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * A TestCase that expects a TestCase to be executed
 * meeting a given time limit.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 */
class PHPUnit2_Extensions_PerformanceTestCase extends PHPUnit2_Framework_TestCase {
    // {{{ Members

    /**
    * @var    double
    * @access private
    */
    private $maxRunningTime = 0;

    // }}}
    // {{{ public function __construct($name, $maxRunningTime = 0)

    /**
    * @param  string $name
    * @param  double $maxRunningTime
    * @access public
    */
    public function __construct($name, $maxRunningTime = 0) {
        if (is_numeric($maxRunningTime) &&
            $maxRunningTime >= 0) {
            $this->maxRunningTime = $maxRunningTime;
        } else {
            throw new Exception('Illegal argument.');
        }

        parent::__construct($name);
    }

    // }}}
    // {{{ protected function runTest()

    /**
    * @access public
    */
    protected function runTest() {
        $timer = new Benchmark_Timer;

        $timer->start();
        parent::runTest();
        $timer->stop();

        if ($this->maxRunningTime != 0 &&
            $timer->timeElapsed() > $this->maxRunningTime) {
            $this->fail(
              sprintf(
                'expected running time: <= %s but was: %s',

                $this->maxRunningTime,
                $timer->timeElapsed()
              )
            );
        }
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
