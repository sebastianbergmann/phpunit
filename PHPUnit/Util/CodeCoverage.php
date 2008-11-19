<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 * @abstract
 */
abstract class PHPUnit_Util_CodeCoverage
{
    protected static $lineToTestMap = array();
    protected static $summary = array();

    /**
     * Returns the names of the covered files.
     *
     * @param  array $data
     * @return array
     */
    public static function getCoveredFiles(array &$data)
    {
        $files = array();

        foreach ($data as $test) {
            $_files = array_keys($test['files']);

            foreach ($_files as $file) {
                if (self::isFile($file) && !in_array($file, $files)) {
                    $files[] = $file;
                }
            }
        }

        return $files;
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
                foreach (self::bitStringToCodeCoverage($test['files'], 1) as $_file => $lines) {
                    foreach ($lines as $_line => $flag) {
                        if ($flag > 0) {
                            if (!isset(self::$lineToTestMap[$_file][$_line])) {
                                self::$lineToTestMap[$_file][$_line] = array($test['test']);
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
     *     linenumber => number of tests that executed the line
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
            $isFileCache = array();

            foreach ($data as $test) {
                if (isset($test['dead'])) {
                    $deadCode       = self::bitStringToCodeCoverage($test['dead'], -2);
                    $executableCode = self::bitStringToCodeCoverage($test['executable'], -1);
                    $executedCode   = self::bitStringToCodeCoverage($test['files'], 1);
                    $keys           = array_merge(array_keys($deadCode), array_keys($executableCode), array_keys($executedCode));
                    $tmp            = array();

                    foreach ($keys as $file) {
                        $tmp[$file] = array();

                        if (isset($executedCode[$file])) {
                            $tmp[$file] += $executedCode[$file];
                        }

                        if (isset($executableCode[$file])) {
                            $tmp[$file] += $executableCode[$file];
                        }

                        if (isset($deadCode[$file])) {
                            $tmp[$file] += $deadCode[$file];
                        }
                    }

                    $test['files'] = $tmp;
                }

                foreach ($test['files'] as $file => $lines) {
                    if (!isset($isFileCache[$file])) {
                        $isFileCache[$file] = self::isFile($file);
                    }

                    if (!$isFileCache[$file]) {
                        continue;
                    }

                    $fileSummary = &self::$summary[$file];

                    foreach ($lines as $line => $flag) {
                        // +1: Line is executable and was executed.
                        if ($flag == 1) {
                            if (isset($fileSummary[$line][0])) {
                                $fileSummary[$line][] = $test['test'];
                            }
                            else {
                                $fileSummary[$line] = array($test['test']);
                            }
                        }

                        // -1: Line is executable and was not executed.
                        // -2: Line is dead code.
                        else if (!isset($fileSummary[$line])) {
                            $fileSummary[$line] = $flag;
                        }
                    }

                    unset($fileSummary);
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
     * @param  string $file
     * @return boolean
     */
    public static function isFile($file)
    {
        if (strpos($file, 'eval()\'d code') ||
            strpos($file, 'runtime-created function') ||
            strpos($file, 'assert code') ||
            strpos($file, 'regexp code')) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     *
     *
     * @since  Method available since Release 3.3.0
     */
    public static function clearSummary()
    {
        self::$summary = array();
    }

    /**
     *
     *
     * @param  array $data
     * @param  array $requiredStatus
     * @return array
     * @since  Method available since Release 3.3.0
     */
    public static function codeCoverageToBitString(array $data, array $requiredStatus)
    {
        $result = array();

        foreach ($data as $file => $coverage) {
            end($coverage);
            $maxLine = key($coverage);

            if ($maxLine == 0) {
                $bitArray = array();
            } else {
                $bitArray = array_fill(0, ceil($maxLine / 8), 0);
            }

            foreach ($coverage as $line => $status) {
                if (!in_array($status, $requiredStatus)) {
                    continue;
                }

                $line--;

                $i             = ($line - ($line % 8)) / 8;
                $bitArray[$i] |= 0x01 << ($line % 8);
            }

            if (isset($line)) {
                $result[$file] = implode('', array_map('chr', $bitArray));
            }
        }

        return $result;
    }

    /**
     *
     *
     * @since  Method available since Release 3.3.0
     */
    public static function bitStringToCodeCoverage($strings, $status)
    {
        $result = array();

        foreach ($strings as $file => $string) {
            if (is_array($string)) {
                return $strings;
            }

            $data   = array();
            $length = strlen($string);

            for ($i = 0; $i < $length; $i++) {
                $ord = ord($string{$i});

                for ($j = 0; $j < 8; $j++) {
                    if ($ord & (0x01 << $j)) {
                        $data[$i * 8 + $j + 1] = $status;
                    }
                }
            }

            $result[$file] = $data;
        }

        return $result;
    }
}
?>
