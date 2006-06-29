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
 * @version    CVS: $Id: Printer.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

/**
 * Utility class that can print to STDOUT or write to a file.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 * @abstract
 */
abstract class PHPUnit2_Util_Printer {
    /**
     * @var    resource
     * @access private
     */
    private $out = NULL;

    /**
     * Constructor.
     *
     * @param  mixed $out
     * @access public
     */
    public function __construct($out = NULL) {
        if ($out !== NULL) {
            if (is_string($out)) {
                $this->out = fopen($out, 'w');
            } else {
                $this->out = $out;
            }
        }
    }

    /**
     * Destructor.
     *
     * @access public
     */
    public function __destruct() {
        if ($this->out !== NULL) {
            fclose($this->out);
        }
    }

    /**
     * @param  string $buffer
     * @access public
     */
    public function write($buffer) {
        if ($this->out !== NULL) {
            fputs($this->out, $buffer);
        } else {
            print $buffer;
        }
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
