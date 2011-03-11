<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Filesystem helpers.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Filesystem
{
    /**
     * @var array
     */
    protected static $buffer = array();

    /**
     * Maps class names to source file names:
     *   - PEAR CS:   Foo_Bar_Baz -> Foo/Bar/Baz.php
     *   - Namespace: Foo\Bar\Baz -> Foo/Bar/Baz.php
     *
     * @param  string $className
     * @return string
     * @since  Method available since Release 3.4.0
     */
    public static function classNameToFilename($className)
    {
        return str_replace(
          array('_', '\\'),
          DIRECTORY_SEPARATOR,
          $className
        ) . '.php';
    }

    /**
     * Starts the collection of loaded files.
     *
     * @since  Method available since Release 3.3.0
     */
    public static function collectStart()
    {
        self::$buffer = get_included_files();
    }

    /**
     * Stops the collection of loaded files and
     * returns the names of the loaded files.
     *
     * @return array
     * @since  Method available since Release 3.3.0
     */
    public static function collectEnd()
    {
        return array_values(
          array_diff(get_included_files(), self::$buffer)
        );
    }

    /**
     * Stops the collection of loaded files and adds
     * the names of the loaded files to the blacklist.
     *
     * @return array
     * @since  Method available since Release 3.4.6
     */
    public static function collectEndAndAddToBlacklist()
    {
        foreach (self::collectEnd() as $blacklistedFile) {
            PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(
              $blacklistedFile, 'PHPUNIT'
            );
        }
    }

    /**
     * Implementation of stream_resolve_include_path() in PHP
     * for version before PHP 5.3.2.
     *
     * @param  string $file
     * @return mixed
     * @author Mattis Stordalen Flister <mattis@xait.no>
     * @since  Method available since Release 3.2.9
     */
    public static function fileExistsInIncludePath($file)
    {
        if (function_exists('stream_resolve_include_path')) {
            return stream_resolve_include_path($file);
        }

        if (file_exists($file)) {
            return realpath($file);
        }

        $paths = explode(PATH_SEPARATOR, get_include_path());

        foreach ($paths as $path) {
            $fullpath = $path . DIRECTORY_SEPARATOR . $file;

            if (file_exists($fullpath)) {
                return realpath($fullpath);
            }
        }

        return FALSE;
    }
}
