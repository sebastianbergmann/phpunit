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
 * @version    CVS: $Id: RepeatedTest.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Extensions/TestDecorator.php';

/**
 * A Decorator that runs a test repeatedly.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_Extensions_RepeatedTest extends PHPUnit2_Extensions_TestDecorator {
    /**
     * @var    integer
     * @access private
     */
    private $timesRepeat = 1;

    /**
     * Constructor.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  integer                 $timesRepeat
     * @throws Exception
     * @access public
     */
    public function __construct(PHPUnit2_Framework_Test $test, $timesRepeat = 1) {
        parent::__construct($test);

        if (is_integer($timesRepeat) &&
            $timesRepeat >= 0) {
            $this->timesRepeat = $timesRepeat;
        } else {
            throw new Exception(
              'Argument 2 must be a positive integer.'
            );
        }
    }

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

    /**
     * Runs the decorated test and collects the
     * result in a TestResult.
     *
     * @param  PHPUnit2_Framework_TestResult $result
     * @return PHPUnit2_Framework_TestResult
     * @throws Exception
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
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
