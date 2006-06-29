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
 * @version    CVS: $Id: TestFailure.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/Test.php';

/**
 * A TestFailure collects a failed test together with the caught exception.
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
class PHPUnit2_Framework_TestFailure {
    /**
     * @var    PHPUnit2_Framework_Test
     * @access protected
     */
    protected $failedTest;

    /**
     * @var    Exception
     * @access protected
     */
    protected $thrownException;

    /**
     * Constructs a TestFailure with the given test and exception.
     *
     * @param  PHPUnit2_Framework_Test $failedTest
     * @param  Exception               $thrownException
     * @access public
     */
    public function __construct(PHPUnit2_Framework_Test $failedTest, Exception $thrownException) {
        $this->failedTest      = $failedTest;
        $this->thrownException = $thrownException;
    }

    /**
     * Returns a short description of the failure.
     *
     * @return string
     * @access public
     */
    public function toString() {
        return sprintf(
          '%s: %s',

          $this->failedTest,
          $this->thrownException->getMessage()
        );
    }

    /**
     * Gets the failed test.
     *
     * @return Test
     * @access public
     */
    public function failedTest() {
        return $this->failedTest;
    }

    /**
     * Gets the thrown exception.
     *
     * @return Exception
     * @access public
     */
    public function thrownException() {
        return $this->thrownException;
    }

    /**
     * Returns the exception's message.
     *
     * @return string
     * @access public
     */
    public function exceptionMessage() {
        return $this->thrownException()->getMessage();
    }

    /**
     * Returns TRUE if the thrown exception
     * is of type AssertionFailedError.
     *
     * @return boolean
     * @access public
     */
    public function isFailure() {
        return ($this->thrownException() instanceof PHPUnit2_Framework_AssertionFailedError);
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
