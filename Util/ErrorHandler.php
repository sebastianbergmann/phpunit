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
 * @version    CVS: $Id: ErrorHandler.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

/**
 * @param  integer $errno
 * @param  string  $errstr
 * @param  string  $errfile
 * @param  integer $errline
 * @throws PHPUnit2_Framework_Error
 * @since  Function available since Release 2.3.0
 */
function PHPUnit2_Util_ErrorHandler($errno, $errstr, $errfile, $errline) {
    $trace = debug_backtrace();
    array_shift($trace);

    throw new PHPUnit2_Framework_Error(
      $errstr,
      $errno,
      $errfile,
      $errline,
      $trace
    );
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
