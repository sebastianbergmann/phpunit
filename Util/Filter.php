<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: Filter.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * Utility class for code filtering.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Util
 */
class PHPUnit2_Util_Filter {
    // {{{ Class Variables

    /**
    * Source files that are to be filtered.
    *
    * @var    array
    * @access protected
    * @static
    */
    protected static $filteredFiles = array(
      'PHPUnit2/Extensions/CodeCoverage/Renderer/HTML.php',
      'PHPUnit2/Extensions/CodeCoverage/Renderer/Text.php',
      'PHPUnit2/Extensions/CodeCoverage/Renderer.php',
      'PHPUnit2/Extensions/Log/PEAR.php',
      'PHPUnit2/Extensions/Log/XML.php',
      'PHPUnit2/Extensions/TestDox/ResultPrinter/HTML.php',
      'PHPUnit2/Extensions/TestDox/ResultPrinter/Text.php',
      'PHPUnit2/Extensions/TestDox/NamePrettifier.php',
      'PHPUnit2/Extensions/TestDox/ResultPrinter.php',
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
      'PHPUnit2/Runner/TestRunListener.php',
      'PHPUnit2/Runner/TestSuiteLoader.php',
      'PHPUnit2/Runner/Version.php',
      'PHPUnit2/TextUI/ResultPrinter.php',
      'PHPUnit2/TextUI/TestRunner.php',
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
      'PEAR.php'
    );

    // }}}
    // {{{ public static function addFileToFilter($filename)

    /**
    * Adds a new file to be filtered.
    *
    * @param  string
    * @access public
    * @static
    * @since  2.1.0
    */
    public static function addFileToFilter($filename) {
        $filename = self::getCanonicalFilename($filename);

        if (!self::isFiltered($filename)) {
            self::$filteredFiles[] = $filename;
        }
    }

    // }}}
    // {{{ public static function removeFileFromFilter($filename)

    /**
    * Removes a file from the filter.
    *
    * @param  string
    * @access public
    * @static
    * @since  2.1.0
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

    // }}}
    // {{{ public static function getFilteredCodeCoverage($codeCoverageInformation)

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

    // }}}
    // {{{ public static function getFilteredStacktrace(Exception $e)

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

    // }}}
    // {{{ protected static function getCanonicalFilename($filename)

    /**
    * Canonicalizes a source file name.
    *
    * @param  string $filename
    * @return string
    * @access public
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

    // }}}
    // {{{ protected static function isFiltered($filename)

    /**
    * @param  string $filename
    * @return boolean
    * @access public
    * @static
    * @since  2.1.3
    */
    protected static function isFiltered($filename) {
        if (substr($filename, -7) == 'phpunit' ||
            in_array(self::getCanonicalFilename($filename), self::$filteredFiles)) {
            return TRUE;
        }

        return FALSE;
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
