<?php
/**
 * PHPUnit
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author     Sebastian Nohn <sebastian@nohn.net>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author     Sebastian Nohn <sebastian@nohn.net>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class Extensions_SeleniumTestCaseTest extends PHPUnit_Extensions_SeleniumTestCase
{
    public function setUp()
    {
        if (!PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_ENABLED) {
            $this->markTestSkipped(
              'The Selenium tests are disabled.'
            );
        }

        if (!class_exists('Testing_Selenium')) {
            $this->markTestSkipped(
              'The PHP bindings for Selenium RC are not installed.'
            );
        }

        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT);
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_BROWSER);
        $this->setBrowserUrl('http://www.example.com/');
        $this->setTimeout(10000);
    }

    public function testAssertLocationEquals()
    {
        $this->open('http://www.example.com/');

        $this->assertLocationEquals('http://www.example.com/');

        try {
            $this->assertLocationEquals('http://www.beispiel.de/');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertLocationNotEquals()
    {
        $this->open('http://www.example.com/');

        $this->assertLocationNotEquals('http://www.beispiel.de/');

        try {
            $this->assertLocationNotEquals('http://www.example.com/');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTitleEquals()
    {
        $this->open('http://www.example.com/');

        $this->assertTitleEquals('Example Web Page');

        try {
            $this->assertTitleEquals('Beispiel Web Seite');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTitleNotEquals()
    {
        $this->open('http://www.example.com/');

        $this->assertTitleNotEquals('Beispiel Web Seite');

        try {
            $this->assertTitleNotEquals('Example Web Page');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTextPresent()
    {
        $this->open('http://www.example.com/');

        $this->assertTextPresent('example.com');

        try {
            $this->assertTextPresent('beispiel.de');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    public function testAssertTextNotPresent()
    {
        $this->open('http://www.example.com/');

        $this->assertTextNotPresent('beispiel.de');

        try {
            $this->assertTextNotPresent('example.com');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }
}
?>
