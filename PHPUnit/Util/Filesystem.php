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
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Filesystem helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 * @abstract
 */
class PHPUnit_Util_Filesystem
{
    /**
     * @var array
     */
    protected static $buffer = array();

    /**
     * @var array
     */
    protected static $hasBinary = array();

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
     * Wrapper for file_exists() that searches the include_path.
     *
     * @param  string $file
     * @return mixed
     * @author Mattis Stordalen Flister <mattis@xait.no>
     * @since  Method available since Release 3.2.9
     */
    public static function fileExistsInIncludePath($file)
    {
        if (file_exists($file) && is_readable($file)) {
            return realpath($file);
        }

        $paths = explode(PATH_SEPARATOR, get_include_path());

        foreach ($paths as $path) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;

            if (file_exists($fullPath) && is_readable($fullPath)) {
                return realpath($fullPath);
            }
        }

        return FALSE;
    }

    /**
     * Returns the common path of a set of files.
     *
     * @param  array $paths
     * @return string
     * @since  Method available since Release 3.1.0
     */
    public static function getCommonPath(array $paths)
    {
        $count = count($paths);

        if ($count == 1) {
            return dirname($paths[0]) . DIRECTORY_SEPARATOR;
        }

        $_paths = array();

        for ($i = 0; $i < $count; $i++) {
            $_paths[$i] = explode(DIRECTORY_SEPARATOR, $paths[$i]);

            if (empty($_paths[$i][0])) {
                $_paths[$i][0] = DIRECTORY_SEPARATOR;
            }
        }

        $common = '';
        $done   = FALSE;
        $j      = 0;
        $count--;

        while (!$done) {
            for ($i = 0; $i < $count; $i++) {
                if ($_paths[$i][$j] != $_paths[$i+1][$j]) {
                    $done = TRUE;
                    break;
                }
            }

            if (!$done) {
                $common .= $_paths[0][$j];

                if ($j > 0) {
                    $common .= DIRECTORY_SEPARATOR;
                }
            }

            $j++;
        }

        return $common;
    }

    /**
     * @param  string $directory
     * @return string
     * @throws RuntimeException
     * @since  Method available since Release 3.3.0
     */
    public static function getDirectory($directory)
    {
        if (substr($directory, -1, 1) != DIRECTORY_SEPARATOR) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        if (is_dir($directory) || mkdir($directory, 0777, TRUE)) {
            return $directory;
        } else {
            throw new RuntimeException(
              sprintf(
                'Directory "%s" does not exist.',
                $directory
              )
            );
        }
    }

    /**
     * Returns a filesystem safe version of the passed filename.
     * This function does not operate on full paths, just filenames.
     *
     * @param  string $filename
     * @return string
     * @author Michael Lively Jr. <m@digitalsandwich.com>
     */
    public static function getSafeFilename($filename)
    {
        /* characters allowed: A-Z, a-z, 0-9, _ and . */
        return preg_replace('#[^\w.]#', '_', $filename);
    }

    /**
     * @param  string $binary
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    public static function hasBinary($binary)
    {
        if (!isset(self::$hasBinary[$binary])) {
            if (substr(php_uname('s'), 0, 7) == 'Windows') {
                $binary .= '.exe';
            }
            
            self::$hasBinary[$binary] = FALSE;

            $openBaseDir = ini_get('open_basedir');

            if (is_string($openBaseDir) && !empty($openBaseDir)) {
                $safeModeExecDir = ini_get('safe_mode_exec_dir');
                $var             = $openBaseDir;

                if (is_string($safeModeExecDir) && !empty($safeModeExecDir)) {
                    $var .= PATH_SEPARATOR . $safeModeExecDir;
                }
            } else {
                if (isset($_ENV['PATH'])) {
                    $var = $_ENV['PATH'];
                }

                else if (isset($_ENV['Path'])) {
                    $var = $_ENV['Path'];
                }

                else if (isset($_SERVER['PATH'])) {
                    $var = $_SERVER['PATH'];
                }

                else if (isset($_SERVER['Path'])) {
                    $var = $_SERVER['Path'];
                }
            }

            if (isset($var)) {
                $paths = explode(PATH_SEPARATOR, $var);
            } else {
                $paths = array();
            }

            foreach ($paths as $path) {
                $_path = $path . DIRECTORY_SEPARATOR . $binary;

                if (file_exists($_path) && is_executable($_path)) {
                    self::$hasBinary[$binary] = TRUE;
                    break;
                }
            }
        }

        return self::$hasBinary[$binary];
    }

    /**
     * Reduces the paths by cutting the longest common start path.
     *
     * For instance,
     *
     * <code>
     * Array
     * (
     *     [/home/sb/PHPUnit/Samples/Money/Money.php] => Array
     *         (
     *             ...
     *         )
     *
     *     [/home/sb/PHPUnit/Samples/Money/MoneyBag.php] => Array
     *         (
     *             ...
     *         )
     * )
     * </code>
     *
     * is reduced to
     *
     * <code>
     * Array
     * (
     *     [Money.php] => Array
     *         (
     *             ...
     *         )
     *
     *     [MoneyBag.php] => Array
     *         (
     *             ...
     *         )
     * )
     * </code>
     *
     * @param  array $files
     * @return string
     * @since  Method available since Release 3.3.0
     */
    public static function reducePaths(&$files)
    {
        if (empty($files)) {
            return '.';
        }

        $commonPath = '';
        $paths      = array_keys($files);

        if (count($files) == 1) {
            $commonPath                 = dirname($paths[0]);
            $files[basename($paths[0])] = $files[$paths[0]];

            unset($files[$paths[0]]);

            return $commonPath;
        }

        $max = count($paths);

        for ($i = 0; $i < $max; $i++) {
            $paths[$i] = explode(DIRECTORY_SEPARATOR, $paths[$i]);

            if (empty($paths[$i][0])) {
                $paths[$i][0] = DIRECTORY_SEPARATOR;
            }
        }

        $done = FALSE;
        $max  = count($paths);

        while (!$done) {
            for ($i = 0; $i < $max - 1; $i++) {
                if (!isset($paths[$i][0]) ||
                    !isset($paths[$i+1][0]) ||
                    $paths[$i][0] != $paths[$i+1][0]) {
                    $done = TRUE;
                    break;
                }
            }

            if (!$done) {
                $commonPath .= $paths[0][0] . (($paths[0][0] != DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '');

                for ($i = 0; $i < $max; $i++) {
                    array_shift($paths[$i]);
                }
            }
        }

        $original = array_keys($files);
        $max      = count($original);

        for ($i = 0; $i < $max; $i++) {
            $files[join('/', $paths[$i])] = $files[$original[$i]];
            unset($files[$original[$i]]);
        }

        ksort($files);

        return $commonPath;
    }
}
?>
