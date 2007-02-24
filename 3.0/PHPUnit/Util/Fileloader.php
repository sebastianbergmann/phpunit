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
 *
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
     * Checks if a PHP sourcefile is readable and contains no syntax errors.
     * If that is the case, the sourcefile is loaded through include_once().
     *
     * @param  string   $filename
     * @throws RuntimeException
     * @access public
     * @static
     */
    public static function checkAndLoad($filename)
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

        self::syntaxCheck($filename);
        self::load($filename);
    }

    /**
     *
     *
     * @return Array
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
     * @param  string   $filename
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
     * @param  string   $filename
     * @throws RuntimeException
     * @access protected
     * @static
     * @since  Method available since Release 3.0.0
     */
    protected static function syntaxCheck($filename)
    {
        $output = shell_exec('php -l ' . escapeshellarg($filename));

        if (strpos($output, 'Errors parsing') === TRUE) {
            throw new RuntimeException($output);
        }
    }
}
?>
