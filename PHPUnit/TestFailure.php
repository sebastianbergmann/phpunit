<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 4
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/PHPUnit
 * @since      File available since Release 1.0.0
 */

/**
 * A TestFailure collects a failed test together with the caught exception.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_TestFailure {
    /**
     * @var    object
     * @access private
     */
    var $_failedTest;

    /**
     * @var    string
     * @access private
     */
    var $_thrownException;

    /**
     * Constructs a TestFailure with the given test and exception.
     *
     * @param  object
     * @param  string
     * @access public
     */
    function PHPUnit_TestFailure(&$failedTest, &$thrownException) {
        $this->_failedTest      = &$failedTest;
        $this->_thrownException = &$thrownException;
    }

    /**
     * Gets the failed test.
     *
     * @return object
     * @access public
     */
    function &failedTest() {
        return $this->_failedTest;
    }

    /**
     * Gets the thrown exception.
     *
     * @return object
     * @access public
     */
    function &thrownException() {
        return $this->_thrownException;
    }

    /**
     * Returns a short description of the failure.
     *
     * @return string
     * @access public
     */
    function toString() {
        return sprintf(
          "TestCase %s->%s() failed: %s\n",

          get_class($this->_failedTest),
          $this->_failedTest->getName(),
          $this->_thrownException
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
