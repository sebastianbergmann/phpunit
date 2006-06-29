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
 * @version    CVS: $Id: PEAR.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit2/Framework/TestListener.php';

@include_once 'Log.php';

/**
 * A TestListener that logs to a PEAR_Log sink.
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
class PHPUnit2_Util_Log_PEAR implements PHPUnit2_Framework_TestListener {
    /**
     * Log.
     *
     * @var    Log
     * @access private
     */
    private $log;

    /**
     * @param string $type      The type of concrete Log subclass to use.
     *                          Currently, valid values are 'console',
     *                          'syslog', 'sql', 'file', and 'mcal'.
     * @param string $name      The name of the actually log file, table, or
     *                          other specific store to use. Defaults to an
     *                          empty string, with which the subclass will
     *                          attempt to do something intelligent.
     * @param string $ident     The identity reported to the log system.
     * @param array  $conf      A hash containing any additional configuration
     *                          information that a subclass might need.
     * @param int $maxLevel     Maximum priority level at which to log.
     * @access public
     */
    public function __construct($type, $name = '', $ident = '', $conf = array(), $maxLevel = PEAR_LOG_DEBUG) {
        $this->log = Log::factory($type, $name, $ident, $conf, $maxLevel);
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->log->crit(
          sprintf(
            'Test "%s" failed: %s',

            $test->getName(),
            $e->getMessage()
          )
        );
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit2_Framework_Test                 $test
     * @param  PHPUnit2_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->log->err(
          sprintf(
            'Test "%s" failed: %s',

            $test->getName(),
            $e->getMessage()
          )
        );
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->log->info(
          sprintf(
            'Test "%s" incomplete: %s',

            $test->getName(),
            $e->getMessage()
          )
        );
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
        $this->log->info(
          sprintf(
            'TestSuite "%s" started.',

            $suite->getName()
          )
        );
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
        $this->log->info(
          sprintf(
            'TestSuite "%s" ended.',

            $suite->getName()
          )
        );
    }

    /**
     * A test started.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit2_Framework_Test $test) {
        $this->log->info(
          sprintf(
            'Test "%s" started.',

            $test->getName()
          )
        );
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function endTest(PHPUnit2_Framework_Test $test) {
        $this->log->info(
          sprintf(
            'Test "%s" ended.',

            $test->getName()
          )
        );
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
