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
 * @author     Sebastian Nohn <sebastian@nohn.net>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Tests for PHPUnit_Extensions_SeleniumTestCase.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author     Sebastian Nohn <sebastian@nohn.net>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
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

        if (!class_exists('Testing_Selenium', FALSE)) {
            $this->markTestSkipped(
              'The PHP bindings for Selenium RC are not installed.'
            );
        }

        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT);
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_BROWSER);
        $this->setBrowserUrl('http://www.openqa.org/');
        $this->setTimeout(10000);
    }

    public function testOpen()
    {
        $this->open('http://www.openqa.org/selenium-core/demo/passing/html/test_open.html');
        $this->assertTextPresent('This is a test of the open command.');
    }

    public function testClick()
    {
        $this->open('http://www.openqa.org/selenium-core/demo/passing/html/test_click_page1.html');
        $this->assertElementContainsText('nextPage', 'Click here for next page');
        $this->clickAndWait('nextPage');
        $this->assertLocationEquals('http://www.openqa.org/selenium-core/demo/passing/html/test_click_page2.html');
        $this->assertTextPresent('This is a test of the click command.');
        $this->clickAndWait('previousPage');
        $this->assertLocationEquals('http://www.openqa.org/selenium-core/demo/passing/html/test_click_page1.html');
    }

    public function testType()
    {
        $this->open('http://www.openqa.org/selenium-core/demo/passing/html/test_type_page1.html');
        $this->assertElementPresent('username');
        $this->type('username', 'TestUser');
        $this->assertElementValueEquals('username', 'TestUser');
        $this->assertElementPresent('password');
        $this->type('password', 'testUserPassword');
        $this->assertElementValueEquals('password', 'testUserPassword');
        $this->clickAndWait('submitButton');
        $this->assertTextPresent('Welcome, TestUser!');
    }

    public function testOpenFail()
    {
        $this->open('http://www.openqa.org/selenium-core/demo/failing/html/test_open.html');

        try {
            $this->assertTextPresent('This test has been modified so it will fail.');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();
    }

    public function testTypeFail()
    {
        $this->open('http://www.openqa.org/selenium-core/demo/failing/html/test_type_page1.html');
        $this->assertElementPresent('username');
        $this->type('username', 'TestUser');
        $this->assertElementValueEquals('username', 'TestUser');
        $this->assertElementPresent('password');
        $this->type('password', 'usersPassword');

        try {
            $this->assertElementValueEquals('password', 'testUserPassword');
        }

        catch (Exception $e) {
            return;
        }

        $this->fail();  
    }

    public function testInPlaceEditor()
    {
        $this->open('http://www.openqa.org/selenium-core/ajaxdemo/scriptaculous-js-1.6.1/test/functional/ajax_inplaceeditor_test.html');
        $this->mouseOver('tobeedited');
        $this->click('tobeedited');
        $this->assertNotVisible('tobeedited');
        $this->assertElementPresent('tobeedited-inplaceeditor');
        $this->click('link=cancel');
        $this->assertElementContainsText('tobeedited', 'To be edited');
        $this->assertVisible('tobeedited');
        $this->click('tobeedited');
        $this->click("//input[@class='editor_ok_button']");
        $this->assertVisible('tobeedited');
#       $this->waitForText('tobeedited', 'Server received: To be edited');
        // Workaround for not yet implemented waitForText
        sleep(1);
        $this->assertElementContainsText('tobeedited', 'Server received: To be edited');
    }
}
?>
