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
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Array.php';
require_once 'PHPUnit/Util/Report/Coverage/Node/Directory.php';
require_once 'PHPUnit/Util/Report/Coverage/Node/File.php';
require_once 'PHPUnit/Util/Report/Test/Node/TestSuite.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Factory for a code coverage information tree.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
abstract class PHPUnit_Util_Report_Coverage_Factory
{
    /**
     * Creates a new Code Coverage information tree.
     *
     * @param  PHPUnit_Framework_TestResult            $result
     * @param  PHPUnit_Util_Report_Test_Node_TestSuite $testSuite
     * @return PHPUnit_Util_Report_Coverage_Node_Directory
     * @access public
     * @static
     */
    public static function create(PHPUnit_Framework_TestResult $result, PHPUnit_Util_Report_Test_Node_TestSuite $testSuite)
    {
        $files      = self::getSummary($result);
        $commonPath = self::reducePaths($files);
        $items      = self::buildDirectoryStructure($files);
        $root       = new PHPUnit_Util_Report_Coverage_Node_Directory($commonPath);

        self::addItems($root, $items, $testSuite, $files);

        return $root;
    }

    /**
     * @param  PHPUnit_Util_Report_Coverage_Node_Directory $root
     * @param  array                                        $items
     * @param  PHPUnit_Util_Report_Test_Node_TestSuite     $testSuite
     * @param  array                                        $files
     * @access protected
     * @static
     */
    protected static function addItems(PHPUnit_Util_Report_Coverage_Node_Directory $root, array $items, PHPUnit_Util_Report_Test_Node_TestSuite $testSuite, array $files)
    {
        foreach ($items as $key => $value) {
            if (substr($key, -2) == '/f') {
                try {
                    $file = $root->addFile(substr($key, 0, -2), $value);
                    $file->setupCoveringTests($testSuite, $files);
                }

                catch (RuntimeException $e) {
                    continue;
                }
            } else {
                $child = $root->addDirectory($key);
                self::addItems($child, $value, $testSuite, $files);
            }
        }
    }

    /**
     * Returns summarized Code Coverage data.
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
     * @param  PHPUnit_Framework_TestResult $result
     * @return array
     * @access protected
     * @static
     * @since  Method available since Release 2.2.0
     */
    protected static function getSummary(PHPUnit_Framework_TestResult $result)
    {
        $summary = array();

        if (!defined('PHPUnit_INSIDE_OWN_TESTSUITE')) {
            $codeCoverageInformation = $result->getCodeCoverageInformation();
        } else {
            $codeCoverageInformation = $result->getCodeCoverageInformation(TRUE, FALSE);
        }

        foreach ($codeCoverageInformation as $test) {
            foreach ($test['files'] as $file => $lines) {
                if (strpos($file, 'eval()\'d code') || strpos($file, 'runtime-created function')) {
                    continue;
                }

                foreach ($lines as $line => $flag) {
                    // +1: Line is executable and was executed.
                    if ($flag == 1) {
                        if (!isset($summary[$file][$line]) ||
                            !is_array($summary[$file][$line])) {
                            $summary[$file][$line] = array();
                        }

                        $summary[$file][$line][] = $test['test'];
                    }

                    // -1: Line is executable and was not executed.
                    // -2: Line is dead code.
                    else if (!(isset($summary[$file][$line]) && is_array($summary[$file][$line]))) {
                        $summary[$file][$line] = $flag;
                    }
                }
            }
        }

        return $summary;
    }

    /**
     * Builds an array representation of the directory structure.
     *
     * For instance,
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
     * is transformed into
     *
     * <code>
     * Array
     * (
     *     [.] => Array
     *         (
     *             [Money.php] => Array
     *                 (
     *                     ...
     *                 )
     *
     *             [MoneyBag.php] => Array
     *                 (
     *                     ...
     *                 )
     *         )
     * )
     * </code>
     *
     * @param  array $files
     * @return array
     * @access protected
     * @static
     */
    protected static function buildDirectoryStructure($files)
    {
        $result = array();

        foreach ($files as $path => $file) {
            $path    = explode('/', $path);
            $pointer = &$result;
            $max     = count($path);

            for ($i = 0; $i < $max; $i++) {
                if ($i == ($max - 1)) {
                    $type = '/f';
                } else {
                    $type = '';
                }

                $pointer = &$pointer[$path[$i] . $type];
            }

            $pointer = $file;
        }

        return $result;
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
     * @access protected
     * @static
     */
    protected static function reducePaths(&$files)
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

        $max = count($paths);

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

        $files = PHPUnit_Util_Array::sortRecursively($files);

        return $commonPath;
    }
}
?>
