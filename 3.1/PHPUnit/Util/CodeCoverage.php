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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
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
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 * @abstract
 */
abstract class PHPUnit_Util_CodeCoverage
{
    /**
     * Returns the names of the covered files.
     *
     * @param  array $codeCoverageInformation
     * @return array
     * @access public
     * @static
     */
    public static function getCoveredFiles(array $codeCoverageInformation)
    {
        $files = array();

        foreach ($codeCoverageInformation as $test) {
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
     * @param  array $codeCoverageInformation
     * @return array
     * @access public
     * @static
     */
    public static function getSummary(array $codeCoverageInformation)
    {
        $summary = array();

        foreach ($codeCoverageInformation as $test) {
            foreach ($test['files'] as $file => $lines) {
                if (!self::isFile($file)) {
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
     * @param  string $file
     * @return boolean
     * @access protected
     * @static
     */
    protected static function isFile($file)
    {
        if (strpos($file, 'eval()\'d code') || strpos($file, 'runtime-created function')) {
            return FALSE;
        }

        return TRUE;
    }
}
?>
