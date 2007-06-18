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
require_once 'PHPUnit/Util/Report/Coverage/Factory.php';
require_once 'PHPUnit/Util/Report/Test/Factory.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 * @abstract
 */
abstract class PHPUnit_Util_Report
{
    /**
     * Renders the report.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  string $target
     * @access public
     * @static
     */
    public static function render(PHPUnit_Framework_TestResult $result, $target)
    {
        if (defined('PHPUnit_INSIDE_OWN_TESTSUITE')) {
            xdebug_stop_code_coverage();
        }

        $tests    = PHPUnit_Util_Report_Test_Factory::create($result);
        $coverage = PHPUnit_Util_Report_Coverage_Factory::create($result, $tests);

        $coverage->render($target, $result->topTestSuite()->getName());
        $tests->render($target, $result->topTestSuite()->getName());

        self::copyFiles($target);
    }

    /**
     * @param  string $target
     * @access protected
     * @static
     */
    protected static function copyFiles($target)
    {
        $files = array(
          'butter.png',
          'chameleon.png',
          'scarlet_red.png',
          'snow.png',
          'style.css',
        );

        $path = self::getTemplatePath();

        foreach ($files as $file) {
            copy($path . $file, $target . $file);
        }
    }

    /**
     * @return string
     * @access public
     * @static
     */
    public static function getTemplatePath()
    {
        return sprintf(
          '%s%sReport%sTemplate%s',

          dirname(__FILE__),
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR
        );
    }
}
?>
