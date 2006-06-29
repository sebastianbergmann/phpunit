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
 * @version    CVS: $Id: ExceptionTestCase.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * A TestCase that expects a specified Exception to be thrown.
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
class PHPUnit2_Extensions_ExceptionTestCase extends PHPUnit2_Framework_TestCase {
    /**
     * The name of the expected Exception.
     *
     * @var    string
     * @access private
     */
    private $expectedException = 'Exception';

    /**
     * @return string
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function getExpectedException() {
        return $this->expectedException;
    }

    /**
     * @param  string  $exceptionName
     * @throws Exception
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function setExpectedException($exceptionName) {
        if (is_string($exceptionName) && class_exists($exceptionName)) {
            $this->expectedException = $exceptionName;
        } else {
            throw new Exception;
        }
    }

    /**
     * @access protected
     */
    protected function runTest() {
        try {
            parent::runTest();
        }

        catch (Exception $e) {
            if ($e instanceof $this->expectedException) {
                return;
            } else {
                throw $e;
            }
        }

        $this->fail('Expected exception ' . $this->expectedException);
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
