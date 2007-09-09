<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Utility methods to load PHP sourcefiles.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.3.0
 */
class PHPUnit_Util_Fileloader
{
    /**
     * Path to the PHP interpreter that is to be used.
     *
     * @var    string $phpBinary
     * @access public
     * @static
     */
    public static $phpBinary = NULL;

    /**
     * Checks if a PHP sourcefile is readable and is optionally checked for
     * syntax errors through the syntaxCheck() method. The sourcefile is
     * loaded through the load() method.
     *
     * @param  string  $filename
     * @param  boolean $syntaxCheck
     * @throws RuntimeException
     * @access public
     * @static
     */
    public static function checkAndLoad($filename, $syntaxCheck = TRUE)
    {
        if (!is_readable($filename)) {
            $filename = './' . $filename;
        }

        if (!is_readable($filename)) {
            throw new RuntimeException(
              sprintf(
                'File "%s" could not be found or is not readable.',

                str_replace('./', '', $filename)
              )
            );
        }

        if ($syntaxCheck) {
            self::syntaxCheck($filename);
        }

        self::load($filename);
    }

    /**
     * Returns the include paths configured via the "include_path"
     * PHP INI setting plus the include paths configured via the
     * PEAR environment's "test_dir" configuration setting.
     *
     * @return array
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function getIncludePaths()
    {
        $includePaths = explode(PATH_SEPARATOR, get_include_path());

        @include_once 'PEAR/Config.php';

        if (class_exists('PEAR_Config', FALSE)) {
            $config         = new PEAR_Config;
            $includePaths[] = $config->get('test_dir');
        }

        return $includePaths;
    }

    /**
     * Loads a PHP sourcefile.
     *
     * When the Xdebug extension is loaded and its "xdebug.collect_vars"
     * configuration directive is enabled, all global variables declared
     * in the loaded PHP sourcefile will be added to $GLOBALS.
     *
     * @param  string $filename
     * @access protected
     * @static
     * @since  Method available since Release 3.0.0
     */
    protected static function load($filename)
    {
        $xdebugLoaded      = extension_loaded('xdebug');
        $xdebugCollectVars = $xdebugLoaded && ini_get('xdebug.collect_vars') == '1';

        if ($xdebugCollectVars) {
            $variables = xdebug_get_declared_vars();
        }

        include_once $filename;

        if ($xdebugCollectVars) {
            $variables = array_values(
              array_diff(xdebug_get_declared_vars(), $variables)
            );

            foreach ($variables as $variable) {
                if (isset($$variable)) {
                    $GLOBALS[$variable] = $$variable;
                }
            }
        }
    }

    /**
     * Uses a separate process to perform a syntax check on a PHP sourcefile.
     *
     * PHPUnit_Util_Fileloader::$phpBinary contains the path to the PHP
     * interpreter used for this. If unset, the following assumptions will
     * be made:
     *
     *   1. When PHPUnit is run using the CLI SAPI and the $_SERVER['_']
     *      variable does not contain the string "PHPUnit", $_SERVER['_']
     *      is assumed to contain the path to the current PHP interpreter
     *      and that will be used.
     *
     *   2. When PHPUnit is run using the CLI SAPI and the $_SERVER['_']
     *      variable contains the string "PHPUnit", the file that $_SERVER['_']
     *      points is assumed to be the PHPUnit TextUI CLI wrapper script
     *      "phpunit" and the binary set up using #! on that file's first
     *      line of code is assumed to contain the path to the current PHP
     *      interpreter and that will be used.
     *
     *   3. The current PHP interpreter is assumed to be in the $PATH and
     *      to be invokable through "php".
     *
     * @param  string $filename
     * @throws RuntimeException
     * @access protected
     * @static
     * @since  Method available since Release 3.0.0
     */
    protected static function syntaxCheck($filename)
    {
        if (self::$phpBinary === NULL) {
            self::$phpBinary = 'php';

            if (PHP_SAPI == 'cli') {
                self::$phpBinary = $_SERVER['_'];

                if (strpos(self::$phpBinary, 'phpunit') !== FALSE) {
                    $file            = file(self::$phpBinary);
                    $tmp             = explode(' ', $file[0]);
                    self::$phpBinary = trim($tmp[1]);
                }
            }
        }

        $output = shell_exec(
          self::$phpBinary . ' -l ' . escapeshellarg($filename)
        );

        if (strpos($output, 'Errors parsing') === TRUE) {
            throw new RuntimeException($output);
        }
    }
}
?>
