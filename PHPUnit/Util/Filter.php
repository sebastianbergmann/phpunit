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
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework/Exception.php';
require_once 'PHPUnit/Util/FilterIterator.php';

/**
 * Utility class for code filtering.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Util_Filter
{
    /**
     * @var    boolean
     */
    public static $addUncoveredFilesFromWhitelist = TRUE;

    /**
     * @var    boolean
     */
    public static $filterPHPUnit = TRUE;

    /**
     * @var    boolean
     */
    protected static $filter = TRUE;

    /**
     * Source files that are blacklisted.
     *
     * @var    array
     */
    protected static $blacklistedFiles = array(
      'DEFAULT' => array(),
      'PHPUNIT' => array(),
      'TESTS' => array()
    );

    /**
     * Source files that are whitelisted.
     *
     * @var    array
     */
    protected static $whitelistedFiles = array();

    /**
     * List of covered files.
     *
     * @var    array
     */
    protected static $coveredFiles = array();

    /**
     * Adds a directory to the blacklist (recursively).
     *
     * @param  string $directory
     * @param  string $suffix
     * @param  string $group
     * @param  string $prefix
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.5
     */
    public static function addDirectoryToFilter($directory, $suffix = '.php', $group = 'DEFAULT', $prefix = '')
    {
        if (file_exists($directory)) {
            foreach (self::getIterator($directory, $suffix, $prefix) as $file) {
                self::addFileToFilter($file->getPathName(), $group);
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $directory . ' does not exist'
            );
        }
    }

    /**
     * Adds a new file to be filtered (blacklist).
     *
     * @param  string $filename
     * @param  string $group
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 2.1.0
     */
    public static function addFileToFilter($filename, $group = 'DEFAULT')
    {
        if (file_exists($filename)) {
            $filename = realpath($filename);

            if (!isset(self::$blacklistedFiles[$group])) {
                self::$blacklistedFiles[$group] = array($filename);
            }

            else if (!in_array($filename, self::$blacklistedFiles[$group])) {
                self::$blacklistedFiles[$group][] = $filename;
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $filename . ' does not exist'
            );
        }
    }

    /**
     * Removes a directory from the blacklist (recursively).
     *
     * @param  string $directory
     * @param  string $suffix
     * @param  string $group
     * @param  string $prefix
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.5
     */
    public static function removeDirectoryFromFilter($directory, $suffix = '.php', $group = 'DEFAULT', $prefix = '')
    {
        if (file_exists($directory)) {
            foreach (self::getIterator($directory, $suffix, $prefix) as $file) {
                self::removeFileFromFilter($file->getPathName(), $group);
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $directory . ' does not exist'
            );
        }
    }

    /**
     * Removes a file from the filter (blacklist).
     *
     * @param  string $filename
     * @param  string $group
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 2.1.0
     */
    public static function removeFileFromFilter($filename, $group = 'DEFAULT')
    {
        if (file_exists($filename)) {
            if (isset(self::$blacklistedFiles[$group])) {
                $filename = realpath($filename);

                foreach (self::$blacklistedFiles[$group] as $key => $_filename) {
                    if ($filename == $_filename) {
                        unset(self::$blacklistedFiles[$group][$key]);
                    }
                }
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $filename . ' does not exist'
            );
        }
    }

    /**
     * Adds a directory to the whitelist (recursively).
     *
     * @param  string $directory
     * @param  string $suffix
     * @param  string $prefix
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.5
     */
    public static function addDirectoryToWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        if (file_exists($directory)) {
            foreach (self::getIterator($directory, $suffix, $prefix) as $file) {
                self::addFileToWhitelist($file->getPathName());
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $directory . ' does not exist'
            );
        }
    }

    /**
     * Adds a new file to the whitelist.
     *
     * When the whitelist is empty (default), blacklisting is used.
     * When the whitelist is not empty, whitelisting is used.
     *
     * @param  string $filename
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.0
     */
    public static function addFileToWhitelist($filename)
    {
        if (file_exists($filename)) {
            $filename = realpath($filename);

            if (!in_array($filename, self::$whitelistedFiles)) {
                self::$whitelistedFiles[] = $filename;
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $filename . ' does not exist'
            );
        }
    }

    /**
     * Removes a directory from the whitelist (recursively).
     *
     * @param  string $directory
     * @param  string $suffix
     * @param  string $prefix
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.5
     */
    public static function removeDirectoryFromWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        if (file_exists($directory)) {
            foreach (self::getIterator($directory, $suffix, $prefix) as $file) {
                self::removeFileFromWhitelist($file->getPathName());
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $directory . ' does not exist'
            );
        }
    }

    /**
     * Removes a file from the whitelist.
     *
     * @param  string $filename
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.0
     */
    public static function removeFileFromWhitelist($filename)
    {
        if (file_exists($filename)) {
            $filename = realpath($filename);

            foreach (self::$whitelistedFiles as $key => $_filename) {
                if ($filename == $_filename) {
                    unset(self::$whitelistedFiles[$key]);
                }
            }
        } else {
            throw new PHPUnit_Framework_Exception(
              $filename . ' does not exist'
            );
        }
    }

    /**
     * Returns data about files within code coverage information, specifically
     * which ones will be filtered out and which ones may be whitelisted but not
     * touched by coverage.
     *
     * Returns a two-item array. The first item is an array indexed by filenames
     * with a boolean payload of whether they should be filtered out.
     *
     * The second item is an array of filenames which are
     * whitelisted but which are absent from the coverage information.
     *
     * @param  array   $codeCoverageInformation
     * @param  boolean $filterTests
     * @return array
     */
    public static function getFileCodeCoverageDisposition(array $codeCoverageInformation, $filterTests = TRUE)
    {
        if (!self::$filter) {
            return array(array(), array());
        }

        $isFilteredCache = array();
        $coveredFiles    = array();

        foreach ($codeCoverageInformation as $k => $test) {
            foreach (array_keys($test['executable']) as $file) {
                if (!isset($isFilteredCache[$file])) {
                    $isFilteredCache[$file] = self::isFiltered(
                      $file, $filterTests
                    );
                }
            }
        }

        $coveredFiles = array_keys($isFilteredCache);
        $missedFiles  = array_diff(self::$whitelistedFiles, $coveredFiles);
        $missedFiles  = array_filter($missedFiles, 'file_exists');

        return array($isFilteredCache, $missedFiles);
    }

    /**
     * @param  array   $codeCoverageInformation
     * @param  boolean $filterTests
     * @return array
     */
    public static function getFilteredCodeCoverage(array $codeCoverageInformation, $filterTests = TRUE)
    {
        if (self::$filter) {
            list($isFilteredCache, $missedFiles) = self::getFileCodeCoverageDisposition(
              $codeCoverageInformation, $filterTests
            );

            foreach ($codeCoverageInformation as $k => $test) {
                foreach (array_keys($test['files']) as $file) {
                    if (isset($isFilteredCache[$file]) &&
                        $isFilteredCache[$file]) {
                        unset($codeCoverageInformation[$k]['files'][$file]);
                    }
                }

                foreach (array_keys($test['dead']) as $file) {
                    if (isset($isFilteredCache[$file]) &&
                        $isFilteredCache[$file]) {
                        unset($codeCoverageInformation[$k]['dead'][$file]);
                    }
                }

                foreach (array_keys($test['executable']) as $file) {
                    if (isset($isFilteredCache[$file]) &&
                        $isFilteredCache[$file]) {
                        unset(
                          $codeCoverageInformation[$k]['executable'][$file]
                        );
                    }
                }
            }

            if (self::$addUncoveredFilesFromWhitelist) {
                foreach (self::$whitelistedFiles as $whitelistedFile) {
                    if (!isset(self::$coveredFiles[$whitelistedFile]) &&
                        !self::isFiltered($whitelistedFile, $filterTests, TRUE)) {
                        if (file_exists($whitelistedFile)) {
                            xdebug_start_code_coverage(
                              XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE
                            );
                            include_once $whitelistedFile;
                            $coverage = xdebug_get_code_coverage();
                            xdebug_stop_code_coverage();

                            foreach ($coverage as $file => $fileCoverage) {
                                if (!in_array($file, self::$whitelistedFiles) ||
                                    isset(self::$coveredFiles[$file])) {
                                    continue;
                                }

                                foreach ($fileCoverage as $line => $flag) {
                                    if ($flag > 0) {
                                        $fileCoverage[$line] = -1;
                                    }
                                }

                                $codeCoverageInformation[] = array(
                                  'test'  => NULL,
                                  'files' => array(
                                    $file => $fileCoverage
                                  )
                                );

                                self::addCoveredFile($file);
                            }
                        }
                    }
                }
            }
        }

        return $codeCoverageInformation;
    }

    /**
     * Filters stack frames from PHPUnit classes.
     *
     * @param  Exception $e
     * @param  boolean   $filterTests
     * @param  boolean   $asString
     * @return string
     */
    public static function getFilteredStacktrace(Exception $e, $filterTests = TRUE, $asString = TRUE)
    {
        if ($asString === TRUE) {
            $filteredStacktrace = '';
        } else {
            $filteredStacktrace = array();
        }

        $eTrace = $e->getTrace();

        if (!self::frameExists($eTrace, $e->getFile(), $e->getLine())) {
            array_unshift(
              $eTrace, array('file' => $e->getFile(), 'line' => $e->getLine())
            );
        }

        foreach ($eTrace as $frame) {
            if (!self::$filter || (isset($frame['file']) &&
                is_file($frame['file']) &&
                !self::isFiltered($frame['file'], $filterTests, TRUE))) {
                if ($asString === TRUE) {
                    $filteredStacktrace .= sprintf(
                      "%s:%s\n",

                      $frame['file'],
                      isset($frame['line']) ? $frame['line'] : '?'
                    );
                } else {
                    $filteredStacktrace[] = $frame;
                }
            }
        }

        return $filteredStacktrace;
    }

    /**
     * Activates or deactivates filtering.
     *
     * @param  boolean $filter
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.0.0
     */
    public static function setFilter($filter)
    {
        if (is_bool($filter)) {
            self::$filter = $filter;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Returns a PHPUnit_Util_FilterIterator that iterates
     * over all files in the given directory that have the
     * given suffix and prefix.
     *
     * @param  string $directory
     * @param  string $suffix
     * @param  string $prefix
     * @return Iterator
     * @since  Method available since Release 3.1.5
     */
    protected static function getIterator($directory, $suffix, $prefix)
    {
        return new PHPUnit_Util_FilterIterator(
          new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
          ),
          $suffix,
          $prefix
        );
    }

    /**
     * @param  string  $filename
     * @param  boolean $filterTests
     * @param  boolean $ignoreWhitelist
     * @return boolean
     * @since  Method available since Release 2.1.3
     */
    public static function isFiltered($filename, $filterTests = TRUE, $ignoreWhitelist = FALSE)
    {
        $filename = realpath($filename);

        if (!$ignoreWhitelist && !empty(self::$whitelistedFiles)) {
            return !in_array($filename, self::$whitelistedFiles);
        }

        $blacklistedFiles = self::$blacklistedFiles['DEFAULT'];

        if ($filterTests) {
            $blacklistedFiles = array_merge(
              $blacklistedFiles, self::$blacklistedFiles['TESTS']
            );
        }

        if (self::$filterPHPUnit) {
            $blacklistedFiles = array_merge(
              $blacklistedFiles, self::$blacklistedFiles['PHPUNIT']
            );
        }

        if (in_array($filename, $blacklistedFiles)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Adds a file to the list of covered files.
     *
     * @param  string  $filename
     * @since  Method available since Release 3.3.0
     */
    public static function addCoveredFile($filename)
    {
        self::$coveredFiles[$filename] = TRUE;
    }

    /**
     * Returns the list of covered files.
     *
     * @return array
     * @since  Method available since Release 3.3.0
     */
    public static function getCoveredFiles()
    {
        return self::$coveredFiles;
    }

    /**
     * Returns the list of blacklisted files.
     *
     * @return array
     * @since  Method available since Release 3.4.3
     */
    public static function getBlacklistedFiles()
    {
        return self::$blacklistedFiles;
    }

    /**
     * @param  array  $trace
     * @param  string $file
     * @param  int    $line
     * @return boolean
     * @since  Method available since Release 3.3.2
     */
    public static function frameExists(array $trace, $file, $line)
    {
        foreach ($trace as $frame) {
            if (isset($frame['file']) && $frame['file'] == $file &&
                isset($frame['line']) && $frame['line'] == $line) {
                return TRUE;
            }
        }

        return FALSE;
    }
}

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
?>