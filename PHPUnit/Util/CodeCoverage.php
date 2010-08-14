<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Code Coverage helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 */
abstract class PHPUnit_Util_CodeCoverage
{
    /**
     * @var array
     */
    protected static $lineToTestMap = array();

    /**
     * @var array
     */
    protected static $summary = array();

    /**
     * Returns only the executed lines.
     *
     * @param  array $data
     * @return array
     * @since  Method available since Release 3.3.15
     */
    public static function getExecutedLines(array $data)
    {
        return self::getLinesByStatus($data, 1);
    }

    /**
     * Returns only the executable lines.
     *
     * @param  array $data
     * @return array
     * @since  Method available since Release 3.3.15
     */
    public static function getExecutableLines(array $data)
    {
        return self::getLinesByStatus($data, array(-1, 1));
    }

    /**
     * Returns only the lines that were not executed.
     *
     * @param  array $data
     * @return array
     * @since  Method available since Release 3.3.15
     */
    public static function getNotExecutedLines(array $data)
    {
        return self::getLinesByStatus($data, -1);
    }

    /**
     * Returns only the dead code lines.
     *
     * @param  array $data
     * @return array
     * @since  Method available since Release 3.3.15
     */
    public static function getDeadLines(array $data)
    {
        return self::getLinesByStatus($data, -2);
    }

    /**
     * Filters lines by status.
     *
     * @param  array         $data
     * @param  array|integer $status
     * @return array
     * @since  Method available since Release 3.3.15
     */
    protected static function getLinesByStatus(array $data, $status)
    {
        if (!is_array($status)) {
            $status = array($status);
        }

        $isFileCache = array();
        $result      = array();

        foreach ($data as $file => $coverage) {
            if (!isset($isFileCache[$file])) {
                $isFileCache[$file] = self::isFile($file);
            }

            if (!$isFileCache[$file]) {
                continue;
            }

            $result[$file] = array();

            foreach ($coverage as $line => $_status) {
                if (in_array($_status, $status)) {
                    $result[$file][$line] = $_status;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the tests that cover a given line.
     *
     * @param  array   $data
     * @param  string  $file
     * @param  string  $line
     * @param  boolean $clear
     * @return array
     */
    public static function getCoveringTests(array &$data, $file, $line, $clear = FALSE)
    {
        if (empty(self::$lineToTestMap) || $clear) {
            foreach ($data as $test) {
                foreach ($test['files'] as $_file => $lines) {
                    foreach ($lines as $_line => $flag) {
                        if ($flag > 0) {
                            if (!isset(self::$lineToTestMap[$_file][$_line])) {
                                self::$lineToTestMap[$_file][$_line] = array(
                                  $test['test']
                                );
                            } else {
                                self::$lineToTestMap[$_file][$_line][] = $test['test'];
                            }
                        }
                    }
                }
            }
        }

        if (isset(self::$lineToTestMap[$file][$line])) {
            return self::$lineToTestMap[$file][$line];
        } else {
            return FALSE;
        }
    }

    /**
     * Returns summarized code coverage data.
     *
     * Format of the result array:
     *
     * <code>
     * array(
     *   "/tested/code.php" => array(
     *     linenumber => array(tests that executed the line)
     *   )
     * )
     * </code>
     *
     * @param  array $data
     * @param  boolean $clear
     * @return array
     */
    public static function getSummary(array &$data, $clear = FALSE)
    {
        if (empty(self::$summary) || $clear) {
            foreach ($data as $test) {
                foreach ($test['files'] as $file => $lines) {
                    foreach ($lines as $line => $flag) {
                        if ($flag == 1) {
                            if (isset(self::$summary[$file][$line][0])) {
                                self::$summary[$file][$line][] = $test['test'];
                            } else {
                                self::$summary[$file][$line] = array(
                                  $test['test']
                                );
                            }
                        }

                        else if (!isset(self::$summary[$file][$line])) {
                            self::$summary[$file][$line] = $flag;
                        }
                    }
                }

                if (isset($test['executable'])) {
                    foreach ($test['executable'] as $file => $lines) {
                        foreach ($lines as $line => $flag) {
                            if ($flag == 1 &&
                                !isset(self::$summary[$file][$line][0])) {
                                self::$summary[$file][$line] = -1;
                            }

                            else if (!isset(self::$summary[$file][$line])) {
                                self::$summary[$file][$line] = $flag;
                            }
                        }
                    }
                }

                if (isset($test['dead'])) {
                    foreach ($test['dead'] as $file => $lines) {
                        foreach ($lines as $line => $flag) {
                            if ($flag == -2 &&
                                !isset(self::$summary[$file][$line][0])) {
                                self::$summary[$file][$line] = -2;
                            }
                        }
                    }
                }
            }
        }

        return self::$summary;
    }

    /**
     * Returns the coverage statistics for a section of a file.
     *
     * @param  array   $data
     * @param  string  $filename
     * @param  integer $startLine
     * @param  integer $endLine
     * @return array
     * @since  Method available since Release 3.2.0
     */
    public static function getStatistics(array &$data, $filename, $startLine = 1, $endLine = FALSE)
    {
        $coverage      = 0;
        $locExecutable = 0;
        $locExecuted   = 0;

        if (isset($data[$filename])) {
            if ($endLine == FALSE) {
                $endLine = count(file($filename));
            }

            foreach ($data[$filename] as $line => $_data) {
                if ($line >= $startLine && $line < $endLine) {
                    if (is_array($_data)) {
                        $locExecutable++;
                        $locExecuted++;
                    }

                    else if ($_data == -1) {
                        $locExecutable++;
                    }
                }
            }

            if ($locExecutable > 0) {
                $coverage = ($locExecuted / $locExecutable) * 100;
            } else {
                $coverage = 100;
            }
        }

        return array(
          'coverage'      => $coverage,
          'loc'           => $endLine - $startLine + 1,
          'locExecutable' => $locExecutable,
          'locExecuted'   => $locExecuted
        );
    }

    /**
     * Checks whether a file (as seen by Xdebug) is actually a file.
     *
     * @param  string $filename
     * @return boolean
     */
    public static function isFile($filename)
    {
        if ($filename == '-' ||
            strpos($filename, 'eval()\'d code') ||
            strpos($filename, 'runtime-created function') ||
            strpos($filename, 'assert code') ||
            strpos($filename, 'regexp code')) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Clears the cached summary information.
     *
     * @since  Method available since Release 3.3.0
     */
    public static function clearSummary()
    {
        self::$summary = array();
    }
}
?>
