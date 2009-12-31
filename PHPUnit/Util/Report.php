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
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Report/Node/Directory.php';
require_once 'PHPUnit/Util/Report/Node/File.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 * @abstract
 */
abstract class PHPUnit_Util_Report
{
    public static $templatePath;

    /**
     * Renders the report.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  string                       $title
     * @param  string                       $target
     * @param  string                       $charset
     * @param  boolean                      $yui
     * @param  boolean                      $highlight
     * @param  integer                      $lowUpperBound
     * @param  integer                      $highLowerBound
     */
    public static function render(PHPUnit_Framework_TestResult $result, $target, $title = '', $charset = 'ISO-8859-1', $yui = TRUE, $highlight = FALSE, $lowUpperBound = 35, $highLowerBound = 70)
    {
        $target = PHPUnit_Util_Filesystem::getDirectory($target);

        self::$templatePath = sprintf(
          '%s%sReport%sTemplate%s',

          dirname(__FILE__),
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR
        );

        $codeCoverageInformation = $result->getCodeCoverageInformation();
        $files                   = PHPUnit_Util_CodeCoverage::getSummary(
                                     $codeCoverageInformation
                                   );
        $commonPath              = PHPUnit_Util_Filesystem::reducePaths($files);
        $items                   = self::buildDirectoryStructure($files);

        unset($codeCoverageInformation);

        if ($title == '') {
            $topTestSuite = $result->topTestSuite();

            if ($topTestSuite instanceof PHPUnit_Framework_TestSuite) {
                $title = $topTestSuite->getName();
            }
        }

        unset($result);

        $root = new PHPUnit_Util_Report_Node_Directory($commonPath, NULL);

        self::addItems($root, $items, $files, $yui, $highlight);
        self::copyFiles($target);

        PHPUnit_Util_CodeCoverage::clearSummary();

        $root->render(
          $target,
          $title,
          $charset,
          $lowUpperBound,
          $highLowerBound
        );
    }

    /**
     * @param  PHPUnit_Util_Report_Node_Directory $root
     * @param  array   $items
     * @param  array   $files
     * @param  boolean $yui
     * @param  boolean $highlight
     */
    protected static function addItems(PHPUnit_Util_Report_Node_Directory $root, array $items, array $files, $yui, $highlight)
    {
        foreach ($items as $key => $value) {
            if (substr($key, -2) == '/f') {
                try {
                    $file = $root->addFile(
                      substr($key, 0, -2), $value, $yui, $highlight
                    );
                }

                catch (RuntimeException $e) {
                    continue;
                }
            } else {
                $child = $root->addDirectory($key);
                self::addItems($child, $value, $files, $yui, $highlight);
            }
        }
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
     * @param  string $target
     */
    protected static function copyFiles($target)
    {
        $files = array(
          'butter.png',
          'chameleon.png',
          'close12_1.gif',
          'container.css',
          'container-min.js',
          'glass.png',
          'scarlet_red.png',
          'snow.png',
          'style.css',
          'yahoo-dom-event.js'
        );

        foreach ($files as $file) {
            copy(self::$templatePath . $file, $target . $file);
        }
    }
}
?>
