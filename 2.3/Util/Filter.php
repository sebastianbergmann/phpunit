<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

/**
 * Utility class for code filtering.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_Util_Filter {
    /**
     * Source files that are to be filtered.
     *
     * @var    array
     * @access protected
     * @static
     */
    protected static $filteredFiles = array(
      'PHPUnit2/Extensions/ExceptionTestCase.php',
      'PHPUnit2/Extensions/PerformanceTestCase.php',
      'PHPUnit2/Extensions/RepeatedTest.php',
      'PHPUnit2/Extensions/TestDecorator.php',
      'PHPUnit2/Extensions/TestSetup.php',
      'PHPUnit2/Framework/Assert.php',
      'PHPUnit2/Framework/AssertionFailedError.php',
      'PHPUnit2/Framework/ComparisonFailure.php',
      'PHPUnit2/Framework/Error.php',
      'PHPUnit2/Framework/IncompleteTest.php',
      'PHPUnit2/Framework/IncompleteTestError.php',
      'PHPUnit2/Framework/Test.php',
      'PHPUnit2/Framework/TestCase.php',
      'PHPUnit2/Framework/TestFailure.php',
      'PHPUnit2/Framework/TestListener.php',
      'PHPUnit2/Framework/TestResult.php',
      'PHPUnit2/Framework/TestSuite.php',
      'PHPUnit2/Framework/Warning.php',
      'PHPUnit2/Runner/BaseTestRunner.php',
      'PHPUnit2/Runner/IncludePathTestCollector.php',
      'PHPUnit2/Runner/StandardTestSuiteLoader.php',
      'PHPUnit2/Runner/TestCollector.php',
      'PHPUnit2/Runner/TestSuiteLoader.php',
      'PHPUnit2/Runner/Version.php',
      'PHPUnit2/TextUI/ResultPrinter.php',
      'PHPUnit2/TextUI/TestRunner.php',
      'PHPUnit2/Util/CodeCoverage/Renderer/HTML.php',
      'PHPUnit2/Util/CodeCoverage/Renderer/Text.php',
      'PHPUnit2/Util/CodeCoverage/Renderer.php',
      'PHPUnit2/Util/Log/PEAR.php',
      'PHPUnit2/Util/Log/XML.php',
      'PHPUnit2/Util/TestDox/ResultPrinter/HTML.php',
      'PHPUnit2/Util/TestDox/ResultPrinter/Text.php',
      'PHPUnit2/Util/TestDox/NamePrettifier.php',
      'PHPUnit2/Util/TestDox/ResultPrinter.php',
      'PHPUnit2/Util/ErrorHandler.php',
      'PHPUnit2/Util/Fileloader.php',
      'PHPUnit2/Util/Filter.php',
      'PHPUnit2/Util/Printer.php',
      'PHPUnit2/Util/Skeleton.php',
      'Benchmark/Timer.php',
      'Console/Getopt.php',
      'Log/composite.php',
      'Log/console.php',
      'Log/display.php',
      'Log/error.php',
      'Log/file.php',
      'Log/mail.php',
      'Log/mcal.php',
      'Log/null.php',
      'Log/observer.php',
      'Log/sql.php',
      'Log/sqlite.php',
      'Log/syslog.php',
      'Log/win.php',
      'Log.php',
      'PEAR/Config.php',
      'PEAR.php'
    );

    /**
     * Adds a new file to be filtered.
     *
     * @param  string
     * @access public
     * @static
     * @since  Method available since Release 2.1.0
     */
    public static function addFileToFilter($filename) {
        $filename = self::getCanonicalFilename($filename);

        if (!self::isFiltered($filename)) {
            self::$filteredFiles[] = $filename;
        }
    }

    /**
     * Removes a file from the filter.
     *
     * @param  string
     * @access public
     * @static
     * @since  Method available since Release 2.1.0
     */
    public static function removeFileFromFilter($filename) {
        $filename = self::getCanonicalFilename($filename);
        $keys     = array_keys(self::$filteredFiles);

        for ($i = 0; $i < sizeof($keys); $i++) {
            if (self::$filteredFiles[$keys[$i]] == $filename) {
                unset(self::$filteredFiles[$keys[$i]]);
                break;
            }
        }
    }

    /**
     * Filters source lines from PHPUnit classes.
     *
     * @param  array
     * @return array
     * @access public
     * @static
     */
    public static function getFilteredCodeCoverage($codeCoverageInformation) {
        $files = array_keys($codeCoverageInformation);

        foreach ($files as $file) {
            if (self::isFiltered($file)) {
                unset($codeCoverageInformation[$file]);
            }
        }

        return $codeCoverageInformation;
    }

    /**
     * Filters stack frames from PHPUnit classes.
     *
     * @param  Exception $e
     * @return string
     * @access public
     * @static
     */
    public static function getFilteredStacktrace(Exception $e) {
        $filteredStacktrace = '';
        $stacktrace         = $e->getTrace();

        foreach ($stacktrace as $frame) {
            $filtered = FALSE;

            if (isset($frame['file']) && !self::isFiltered($frame['file'])) {
                $filteredStacktrace .= sprintf(
                  "%s:%s\n",

                  $frame['file'],
                  isset($frame['line']) ? $frame['line'] : '?'
                );
            }
        }

        return $filteredStacktrace;
    }

    /**
     * Canonicalizes a source file name.
     *
     * @param  string $filename
     * @return string
     * @access protected
     * @static
     */
    protected static function getCanonicalFilename($filename) {
        foreach (array('PHPUnit2', 'Benchmark', 'Console', 'PEAR') as $package) {
            $pos = strpos($filename, $package);

            if ($pos !== FALSE) {
                $filename = substr($filename, $pos);
                break;
            }
        }

        return str_replace(
          '\\',
          '/',
          $filename
        );
    }

    /**
     * @param  string $filename
     * @return boolean
     * @access protected
     * @static
     * @since  Method available since Release 2.1.3
     */
    protected static function isFiltered($filename) {
        if (substr($filename, -7) == 'phpunit' ||
            in_array(self::getCanonicalFilename($filename), self::$filteredFiles)) {
            return TRUE;
        }

        return FALSE;
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
