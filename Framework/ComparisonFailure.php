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
 * @version    CVS: $Id: ComparisonFailure.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/AssertionFailedError.php';

/**
 * Thrown when an assertion for string equality failed.
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
class PHPUnit2_Framework_ComparisonFailure extends PHPUnit2_Framework_AssertionFailedError {
    /**
     * @var    string
     * @access private
     */
    private $expected = '';

    /**
     * @var    string
     * @access private
     */
    private $actual = '';

    /**
     * Constructs a comparison failure.
     *
     * @param  string $expected
     * @param  string $actual
     * @param  string $message
     * @access public
     */
    public function __construct($expected, $actual, $message = '') {
        parent::__construct($message);

        $this->expected = ($expected === NULL) ? 'NULL' : $expected;
        $this->actual   = ($actual   === NULL) ? 'NULL' : $actual;
    }

    /**
     * Returns "..." in place of common prefix and "..." in
     * place of common suffix between expected and actual.
     *
     * @return string
     * @access public
     */
    public function toString() {
        $end = min(strlen($this->expected), strlen($this->actual));
        $i   = 0;
        $j   = strlen($this->expected) - 1;
        $k   = strlen($this->actual)   - 1;

        for (; $i < $end; $i++) {
            if ($this->expected[$i] != $this->actual[$i]) {
                break;
            }
        }

        for (; $k >= $i && $j >= $i; $k--,$j--) {
            if ($this->expected[$j] != $this->actual[$k]) {
                break;
            }
        }

        if ($j < $i && $k < $i) {
            $expected = $this->expected;
            $actual   = $this->actual;
        } else {
            $expected = substr($this->expected, $i, ($j + 1 - $i));
            $actual   = substr($this->actual,   $i, ($k + 1 - $i));;

            if ($i <= $end && $i > 0) {
                $expected = '...' . $expected;
                $actual   = '...' . $actual;
            }
      
            if ($j < strlen($this->expected) - 1) {
                $expected .= '...';
            }

            if ($k < strlen($this->actual) - 1) {
                $actual .= '...';
            }
        }

        return PHPUnit2_Framework_Assert::format(
            $expected,
            $actual,
            parent::getMessage()
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
