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
 * @since      File available since Release 3.4.0
 */

require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Diff helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Util_Diff
{
    /**
     * @var boolean
     */
    protected static $hasTextDiff = NULL;

    /**
     * @var boolean
     */
    protected static $hasDiffCommand = NULL;

    /**
     * @param  string $from
     * @param  string $to
     * @return string
     */
    public static function diff($from, $to)
    {
        self::setUp();

        if (self::$hasTextDiff) {
            $result = self::doTextDiff($from, $to);
        }

        else if (self::$hasDiffCommand) {
            $result = self::doDiffCommand($from, $to);
        }

        if (isset($result)) {
            $result = explode("\n", $result);

            if (self::$hasTextDiff) {
                array_unshift($result, '--- Expected', '+++ Actual');
            }

            else if (self::$hasDiffCommand) {
                $result[0] = '--- Expected';
                $result[1] = '+++ Actual';
            }

            if (empty($result[count($result)-1])) {
                unset($result[count($result)-1]);
            }

            return implode("\n", $result);
        }

        return FALSE;
    }

    /**
     * @param  string $from
     * @param  string $to
     * @return string
     */
    protected static function doTextDiff($from, $to)
    {
        $from = explode("\n", $from);
        $to   = explode("\n", $to);

        $currentErrorReporting = error_reporting(E_ERROR | E_WARNING | E_PARSE);
        PHPUnit_Util_Filesystem::collectStart();

        $renderer = new Text_Diff_Renderer_unified;
        $diff     = $renderer->render(new Text_Diff($from, $to));

        foreach (PHPUnit_Util_Filesystem::collectEnd() as $blacklistedFile) {
            PHPUnit_Util_Filter::addFileToFilter($blacklistedFile, 'PHPUNIT');
        }

        error_reporting($currentErrorReporting);

        return $diff;
    }

    /**
     * @param  string $from
     * @param  string $to
     * @return string
     */
    protected static function doDiffCommand($from, $to)
    {
        if (defined('PHPUNIT_TMPDIR')) {
            $tmpDir = PHPUNIT_TMPDIR;
        } else {
            $tmpDir = sys_get_temp_dir();
        }

        $fromFile = tempnam($tmpDir, 'expected');
        $toFile   = tempnam($tmpDir, 'actual');

        file_put_contents($fromFile, $from);
        file_put_contents($toFile, $to);

        $buffer = shell_exec(
          sprintf(
            'diff -u %s %s',
            escapeshellarg($fromFile),
            escapeshellarg($toFile)
          )
        );

        unlink($fromFile);
        unlink($toFile);

        return $buffer;
    }

    /**
     * - Tries to find the Text_Diff PEAR package.
     * - Tries to find the "diff" command.
     */
    protected static function setUp()
    {
        if (self::$hasTextDiff === NULL) {
            if (PHPUnit_Util_Filesystem::fileExistsInIncludePath('Text/Diff.php')) {
                $currentErrorReporting = error_reporting(E_ERROR | E_WARNING | E_PARSE);
                PHPUnit_Util_Filesystem::collectStart();
                require_once 'Text/Diff.php';
                require_once 'Text/Diff/Renderer/unified.php';
                error_reporting($currentErrorReporting);

                foreach (PHPUnit_Util_Filesystem::collectEnd() as $blacklistedFile) {
                    PHPUnit_Util_Filter::addFileToFilter($blacklistedFile, 'PHPUNIT');
                }
            }
        }

        if (class_exists('Text_Diff', FALSE)) {
            self::$hasTextDiff = TRUE;
        } else {
            self::$hasTextDiff = FALSE;
        }

        if (!self::$hasTextDiff && self::$hasDiffCommand === NULL) {
            self::$hasDiffCommand = PHPUnit_Util_Filesystem::hasBinary('diff');
        }
    }
}
?>
