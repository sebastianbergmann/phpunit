<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: PerformanceTestCase.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.1.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'Benchmark/Timer.php';

/**
 * A TestCase that expects a TestCase to be executed
 * meeting a given time limit.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.1.0
 */
class PHPUnit2_Extensions_PerformanceTestCase extends PHPUnit2_Framework_TestCase {
    /**
     * @var    integer
     * @access private
     */
    private $maxRunningTime = 0;

    /**
     * @access protected
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

    /**
     * @param  integer $maxRunningTime
     * @throws Exception
     * @access public
     * @since  Method available since Release 2.3.0
     */
    public function setMaxRunningTime($maxRunningTime) {
        if (is_integer($maxRunningTime) &&
            $maxRunningTime >= 0) {
            $this->maxRunningTime = $maxRunningTime;
        } else {
            throw new Exception;
        }
    }

    /**
     * @return integer
     * @access public
     * @since  Method available since Release 2.3.0
     */
    public function getMaxRunningTime() {
        return $this->maxRunningTime;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
