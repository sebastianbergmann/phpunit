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
 * @version    CVS: $Id: Fileloader.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

/**
 * 
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.3.0
 */
class PHPUnit2_Util_Fileloader {
    /**
     * Checks if a PHP sourcefile is readable and contains no syntax errors.
     * If that is the case, the sourcefile is loaded through include_once().
     *
     * @param  string   $filename
     * @throws Exception
     * @access public
     * @static
     */
    public static function checkAndLoad($filename) {
        if (!is_readable($filename)) {
            $filename = './' . $filename;
        }

        if (!is_readable($filename)) {
            throw new Exception(
              sprintf(
                '%s could not be found or is not readable.',

                str_replace('./', '', $filename)
              )
            );
        }

        $output = shell_exec('php -l ' . $filename);

        if (strpos($output, 'No syntax errors detected in') === FALSE) {
            throw new Exception(
              sprintf(
                'Syntax error in %s.',

                str_replace('./', '', $filename)
              )
            );
        }

        include_once $filename;
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
