<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit/Util/InvalidArgumentHelper.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Filesystem.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Utility methods to load PHP sourcefiles.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
     */
    public static function checkAndLoad($filename, $syntaxCheck = TRUE)
    {
        if (!is_readable($filename)) {
            $filename = './' . $filename;
        }

        if (!is_readable($filename)) {
            throw new RuntimeException(
              sprintf(
                'File "%s" does not exist.',
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
     * Loads a PHP sourcefile.
     *
     * @param  string $filename
     * @return mixed
     * @since  Method available since Release 3.0.0
     */
    public static function load($filename)
    {
        $filename = PHPUnit_Util_Filesystem::fileExistsInIncludePath($filename);

        if (!$filename) {
            throw new RuntimeException(
              sprintf(
                'File "%s" does not exist.',
                $filename
              )
            );
        }

        $oldVariableNames = array_keys(get_defined_vars());

        include_once $filename;

        $newVariables     = get_defined_vars();
        $newVariableNames = array_diff(array_keys($newVariables), $oldVariableNames);

        foreach ($newVariableNames as $variableName) {
            if ($variableName != 'oldVariableNames') {
                $GLOBALS[$variableName] = $newVariables[$variableName];
            }
        }

        return $filename;
    }

    /**
     * Uses a separate process to perform a syntax check on a PHP sourcefile.
     *
     * PHPUnit_Util_Fileloader::$phpBinary contains the path to the PHP
     * interpreter used for this. If unset, the following assumptions will
     * be made:
     *
     *   1. When the PHP CLI/CGI binary configured with the PEAR Installer
     *      (php_bin configuration value) is readable, it will be used.
     *
     *   2. When PHPUnit is run using the CLI SAPI and the $_SERVER['_']
     *      variable does not contain the string "PHPUnit", $_SERVER['_']
     *      is assumed to contain the path to the current PHP interpreter
     *      and that will be used.
     *
     *   3. When PHPUnit is run using the CLI SAPI and the $_SERVER['_']
     *      variable contains the string "PHPUnit", the file that $_SERVER['_']
     *      points is assumed to be the PHPUnit TextUI CLI wrapper script
     *      "phpunit" and the binary set up using #! on that file's first
     *      line of code is assumed to contain the path to the current PHP
     *      interpreter and that will be used.
     *
     *   4. The current PHP interpreter is assumed to be in the $PATH and
     *      to be invokable through "php".
     *
     * @param  string $filename
     * @throws RuntimeException
     * @since  Method available since Release 3.0.0
     */
    protected static function syntaxCheck($filename)
    {
        if (self::$phpBinary === NULL) {
            if (is_readable('@php_bin@')) {
                self::$phpBinary = '@php_bin@';
            }

            else if (PHP_SAPI == 'cli' && isset($_SERVER['_']) &&
                     strpos($_SERVER['_'], 'phpunit') !== FALSE) {
                $file            = file($_SERVER['_']);
                $tmp             = explode(' ', $file[0]);
                self::$phpBinary = trim($tmp[1]);
            }

            if (!is_readable(self::$phpBinary)) {
                self::$phpBinary = 'php';
            } else {
                self::$phpBinary = escapeshellarg(self::$phpBinary);
            }
        }

        $command = self::$phpBinary . ' -l ' . escapeshellarg($filename);

        if (DIRECTORY_SEPARATOR == '\\') {
            $command = '"' . $command . '"';
        }

        $output = shell_exec($command);

        if (strpos($output, 'Errors parsing') !== FALSE) {
            throw new RuntimeException($output);
        }
    }
}
?>
