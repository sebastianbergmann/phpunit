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
 * @since      File available since Release 2.0.0
 */

/**
 * Utility class for code filtering.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Util_Filter
{
    /**
     * @var    boolean
     * @access private
     * @static
     */
    private static $filter = TRUE;

    /**
     * Source files that are to be filtered.
     *
     * @var    array
     * @access protected
     * @static
     */
    protected static $filteredFiles = array(
      'DEFAULT' => array(),
      'PHPUNIT' => array(),
      'TESTS' => array(),
      'PEAR' => array(
        'Image/GraphViz.php',
        'Log/composite.php',
        'Log/console.php',
        'Log/daemon.php',
        'Log/display.php',
        'Log/error_log.php',
        'Log/file.php',
        'Log/mail.php',
        'Log/mcal.php',
        'Log/mdb2.php',
        'Log/null.php',
        'Log/observer.php',
        'Log/sql.php',
        'Log/sqlite.php',
        'Log/syslog.php',
        'Log/win.php',
        'Log.php',
        'Testing/Selenium/Exception.php',
        'Testing/Selenium.php',
        'PEAR/Config.php',
        'PEAR.php'
      )
    );

    /**
     * Adds a new file to be filtered.
     *
     * @param  string $filename
     * @param  string $group
     * @access public
     * @static
     * @since  Method available since Release 2.1.0
     */
    public static function addFileToFilter($filename, $group = 'DEFAULT')
    {
        self::$filteredFiles[$group][] = self::getCanonicalFilename($filename);
    }

    /**
     * Removes a file from the filter.
     *
     * @param  string $filename
     * @param  string $group
     * @access public
     * @static
     * @since  Method available since Release 2.1.0
     */
    public static function removeFileFromFilter($filename, $group = 'DEFAULT')
    {
        if (isset(self::$filteredFiles[$group])) {
            $filename = self::getCanonicalFilename($filename);

            foreach (self::$filteredFiles[$group] as $key => $_filename) {
                if ($filename == $_filename) {
                    unset(self::$filteredFiles[$group][$key]);
                }
            }
        }
    }

    /**
     * Filters source lines from PHPUnit classes.
     *
     * @param  array   $codeCoverageInformation
     * @param  boolean $filterTests
     * @param  boolean $filterPHPUnit
     * @return array
     * @access public
     * @static
     */
    public static function getFilteredCodeCoverage(array $codeCoverageInformation, $filterTests = TRUE, $filterPHPUnit = TRUE)
    {
        if (self::$filter) {
            $max = count($codeCoverageInformation);

            for ($i = 0; $i < $max; $i++) {
                foreach (array_keys($codeCoverageInformation[$i]['files']) as $file) {
                    if (self::isFiltered($file, $filterTests, $filterPHPUnit)) {
                        unset($codeCoverageInformation[$i]['files'][$file]);
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
     * @param  boolean   $filterPHPUnit
     * @param  boolean   $asString
     * @return string
     * @access public
     * @static
     */
    public static function getFilteredStacktrace(Exception $e, $filterTests = TRUE, $filterPHPUnit = TRUE, $asString = TRUE)
    {
        if ($asString === TRUE) {
            $filteredStacktrace = '';
        } else {
            $filteredStacktrace = array();
        }

        foreach ($e->getTrace() as $frame) {
            if (!self::$filter || (isset($frame['file']) && !self::isFiltered($frame['file'], $filterTests, $filterPHPUnit))) {
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
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function setFilter($filter)
    {
        if (is_bool($filter)) {
            self::$filter = $filter;
        } else {
            throw new InvalidArgumentException;
        }
    }

    /**
     * Canonicalizes a source file name.
     *
     * @param  string $filename
     * @return string
     * @access protected
     * @static
     */
    protected static function getCanonicalFilename($filename)
    {
        return str_replace('\\', '/', $filename);
    }

    /**
     * @param  string  $filename
     * @param  boolean $filterTests
     * @param  boolean $filterPHPUnit
     * @return boolean
     * @access protected
     * @static
     * @since  Method available since Release 2.1.3
     */
    protected static function isFiltered($filename, $filterTests = TRUE, $filterPHPUnit = TRUE)
    {
        $filename = self::getCanonicalFilename($filename);

        $filteredFiles = array_merge(
          self::$filteredFiles['DEFAULT'],
          self::$filteredFiles['PEAR']
        );

        if ($filterTests) {
            $filteredFiles = array_merge(
              $filteredFiles,
              self::$filteredFiles['TESTS']
            );
        }

        if ($filterPHPUnit) {
            $filteredFiles = array_merge(
              $filteredFiles,
              self::$filteredFiles['PHPUNIT']
            );
        }

        if (in_array($filename, $filteredFiles)) {
            return TRUE;
        }

        foreach ($filteredFiles as $filteredFile) {
            if (strpos($filename, $filteredFile) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }
}

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
?>
