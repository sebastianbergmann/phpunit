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
 * @author     Shin Ohno <ganchiku@gmail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
 * @author     Shin Ohno <ganchiku@gmail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class Extensions_SeleniumTestCaseTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected $url;

    public function setUp()
    {
        if (!@fsockopen(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST, PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT, $errno, $errstr)) {
            $this->markTestSkipped(
              sprintf(
                'Selenium RC not running on %s:%d.',
                PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST,
                PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT
              )
            );
        }

        $this->url = sprintf(
          'http://%s:%d/selenium-server/tests/',
          PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST,
          PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT
        );

        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort((int)PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT);
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_BROWSER);
        $this->setBrowserUrl($this->url);
    }

    public function testOpen()
    {
        $this->open($this->url . 'html/test_open.html');
        $this->assertStringEndsWith('html/test_open.html', $this->getLocation());
        $this->assertEquals('This is a test of the open command.', $this->getBodyText());

        $this->open($this->url . 'html/test_page.slow.html');
        $this->assertStringEndsWith('html/test_page.slow.html', $this->getLocation());
        $this->assertEquals('Slow Loading Page', $this->getTitle());
    }

    public function testClick()
    {
        $this->open($this->url . 'html/test_click_page1.html');
        $this->assertEquals('Click here for next page', $this->getText('link'));
        $this->click('link');
        $this->waitForPageToLoad(500);
        $this->assertEquals('Click Page Target', $this->getTitle());
        $this->click('previousPage');
        $this->waitForPageToLoad(500);
        $this->assertEquals('Click Page 1', $this->getTitle());

        $this->click('linkWithEnclosedImage');
        $this->waitForPageToLoad(500);
        $this->assertEquals('Click Page Target', $this->getTitle());
        $this->click('previousPage');
        $this->waitForPageToLoad(500);

        $this->click('enclosedImage');
        $this->waitForPageToLoad(500);
        $this->assertEquals('Click Page Target', $this->getTitle());
        $this->click('previousPage');
        $this->waitForPageToLoad(500);

        $this->click('linkToAnchorOnThisPage');
        $this->assertEquals('Click Page 1', $this->getTitle());
        $this->click('linkWithOnclickReturnsFalse');
        $this->assertEquals('Click Page 1', $this->getTitle());

    }

    public function testClickJavaScriptHref()
    {
        $this->open($this->url . 'html/test_click_javascript_page.html');
        $this->click('link');
        $this->assertTrue($this->isAlertPresent());
        $this->assertEquals('link clicked: foo', $this->getAlert());

        $this->click('linkWithMultipleJavascriptStatements');
        $this->assertEquals('alert1', $this->getAlert());
        $this->assertEquals('alert2', $this->getAlert());
        $this->assertEquals('alert3', $this->getAlert());

        $this->click('linkWithJavascriptVoidHref');
        $this->assertEquals('onclick', $this->getAlert());
        $this->assertEquals('Click Page 1', $this->getTitle());

        $this->click('linkWithOnclickReturnsFalse');
        $this->assertEquals('Click Page 1', $this->getTitle());

        $this->click('enclosedImage');
        $this->assertEquals('enclosedImage clicked', $this->getAlert());
    }

    public function testType()
    {
        $this->open($this->url . 'html/test_type_page1.html');
        $this->type('username', 'TestUser');
        $this->assertEquals('TestUser', $this->getValue('username'));
        $this->type('password', 'testUserPassword');
        $this->assertEquals('testUserPassword', $this->getValue('password'));

        $this->click('submitButton');
        $this->waitForPageToLoad(500);
        $this->assertRegExp('/Welcome, TestUser!/', $this->getText('//h2'));
    }

    public function testSelect()
    {
        $this->open($this->url . 'html/test_select.html');
        $this->assertEquals('Second Option', $this->getSelectedLabel('theSelect'));
        $this->assertEquals('option2', $this->getSelectedValue('theSelect'));

        $this->select('theSelect', 'index=4');
        $this->assertEquals('Fifth Option', $this->getSelectedLabel('theSelect'));
        $this->assertEquals('o4', $this->getSelectedId('theSelect'));

        $this->select('theSelect', 'Third Option');
        $this->assertEquals('Third Option', $this->getSelectedLabel('theSelect'));

        $this->select('theSelect', 'label=Fourth Option');
        $this->assertEquals('Fourth Option', $this->getSelectedLabel('theSelect'));

        $this->select('theSelect', 'value=option6');
        $this->assertEquals('Sixth Option', $this->getSelectedLabel('theSelect'));

        $this->select('theSelect', 'value=');
        $this->assertEquals('Empty Value Option', $this->getSelectedLabel('theSelect'));

        $this->select('theSelect', 'id=o4');
        $this->assertEquals('Fourth Option', $this->getSelectedLabel('theSelect'));

        $this->select('theSelect', '');
        $this->assertEquals('', $this->getSelectedLabel('theSelect'));

    }

    public function testMultiSelect()
    {
        $this->open($this->url . 'html/test_multiselect.html');
        $this->assertEquals('Second Option', $this->getSelectedLabel('theSelect'));

        $this->select('theSelect', 'index=4');
        $this->assertEquals('Fifth Option', $this->getSelectedLabel('theSelect'));

        $this->addSelection('theSelect', 'Third Option');
        $this->addSelection('theSelect', 'value=');
        $this->assertTrue(in_array('Third Option', $this->getSelectedLabels('theSelect')));
        $this->assertTrue(in_array('Fifth Option', $this->getSelectedLabels('theSelect')));
        $this->assertTrue(in_array('Empty Value Option', $this->getSelectedLabels('theSelect')));
        $this->assertEquals(3, count($this->getSelectedLabels('theSelect')));

        $this->removeSelection('theSelect', 'id=o7');
        $this->assertFalse(in_array('Empty Value Option', $this->getSelectedLabels('theSelect')));
        $this->assertEquals(2, count($this->getSelectedLabels('theSelect')));

        $this->removeSelection('theSelect', 'label=Fifth Option');
        $this->assertFalse(in_array('Fifth Option', $this->getSelectedLabels('theSelect')));
        $this->assertEquals(1, count($this->getSelectedLabels('theSelect')));

        $this->addSelection('theSelect', '');
        $this->assertEquals(2, count($this->getSelectedLabels('theSelect')));
    }

    public function testSubmit()
    {
        $this->open($this->url . 'html/test_submit.html');
        $this->submit('searchForm');
        $this->assertTrue($this->isAlertPresent());
        $this->assertEquals('onsubmit called', $this->getAlert());

        $this->check('okayToSubmit');
        $this->submit('searchForm');
        $this->assertEquals('onsubmit called', $this->getAlert());
        $this->assertEquals('form submitted', $this->getAlert());
    }

    public function testCheckUncheck()
    {
        $this->open($this->url . 'html/test_check_uncheck.html');
        $this->assertEquals('on', $this->getValue('base-spud'));
        $this->assertNotEquals('on', $this->getValue('base-rice'));
        $this->assertEquals('on', $this->getValue('option-cheese'));
        $this->assertNotEquals('on', $this->getValue('option-onions'));

        $this->check('base-rice');
        $this->assertNotEquals('on', $this->getValue('base-spud'));
        $this->assertEquals('on', $this->getValue('base-rice'));
        $this->uncheck('option-cheese');
        $this->assertEquals('off', $this->getValue('option-cheese'));
        $this->check('option-onions');
        $this->assertNotEquals('off', $this->getValue('option-onions'));

        $this->assertNotEquals('on', $this->getValue('option-chilli'));
        $this->check('option-chilli');
        $this->assertEquals('on', $this->getValue('option-chilli'));
        $this->uncheck('option index=3');
        $this->assertNotEquals('on', $this->getValue('option-chilli'));
    }

    public function testSelectWindow()
    {
        $this->open($this->url . 'html/test_select_window.html');
        $this->click('popupPage');
        $this->waitForPopUp('myPopupWindow', 1000);
        $this->selectWindow('myPopupWindow');
        $this->assertStringEndsWith('html/test_select_window_popup.html', $this->getLocation());
        $this->assertEquals('Select Window Popup', $this->getTitle());
        $this->close();
        $this->selectWindow('null');

        $this->assertStringEndsWith('html/test_select_window.html', $this->getLocation());
        $this->click('popupPage');
        $this->waitForPopUp('myNewWindow', 1000);
        $this->selectWindow('myNewWindow');
        $this->assertStringEndsWith('html/test_select_window_popup.html', $this->getLocation());
        $this->close();
        $this->selectWindow('null');

        $this->click('popupAnonymous');
        $this->waitForPopUp('anonymouspopup', 1000);
        $this->selectWindow('anonymouspopup');
        $this->assertStringEndsWith('html/test_select_window_popup.html', $this->getLocation());
        $this->click('closePage');
    }

    public function testJavaScriptParameters()
    {
        $this->open($this->url . 'html/test_store_value.html');
        $this->type('theText', "javascript{[1,2,3,4,5].join(':')}");
        $this->assertEquals('1:2:3:4:5', $this->getValue('theText'));

        $this->type('theText', 'javascript{10 * 5}');
        $this->assertEquals('50', $this->getValue('theText'));
    }

    public function testWait()
    {
        $this->open($this->url . 'html/test_reload_onchange_page.html');
        $this->select('theSelect', 'Second Option');
        $this->waitForPageToLoad(5000);
        $this->assertEquals('Slow Loading Page', $this->getTitle());
        $this->goBack();
        $this->waitForPageToLoad(5000);

        $this->type('theTextbox', 'new value');
        $this->fireEvent('theTextbox', 'blur');
        $this->waitForPageToLoad(5000);
        $this->assertEquals('Slow Loading Page', $this->getTitle());

        $this->goBack();
        $this->waitForPageToLoad(5000);

        $this->click('theSubmit');
        $this->waitForPageToLoad(5000);
        $this->assertEquals('Slow Loading Page', $this->getTitle());

        $this->click('slowPage_reload');
        $this->waitForPageToLoad(5000);
        $this->assertEquals('Slow Loading Page', $this->getTitle());
    }

    public function testWaitInPopupWindow()
    {
        $this->open($this->url . 'html/test_select_window.html');
        $this->click('popupPage');
        $this->waitForPopUp('myPopupWindow', 500);
        $this->selectWindow('myPopupWindow');
        $this->assertEquals('Select Window Popup', $this->getTitle());

        $this->setTimeout(2000);
        $this->click('link=Click to load new page');
        // XXX NEED TO CHECK
        $this->waitForPageToLoad(2000);
        $this->assertEquals('Reload Page', $this->getTitle());

        $this->setTimeout(30000);
        $this->click('link=Click here');
        // XXX NEED TO CHECK
        $this->waitForPageToLoad(30000);
        $this->assertEquals('Slow Loading Page', $this->getTitle());

        $this->close();
        $this->selectWindow('null');
    }

    public function testLocators()
    {
        $this->open($this->url . 'html/test_locators.html');
        $this->assertEquals('this is the first element', $this->getText('id=id1'));
        $this->assertFalse($this->isElementPresent('id=name1'));
        $this->assertFalse($this->isElementPresent('id=id4'));
        $this->assertEquals('a1', $this->getAttribute('id=id1@class'));

        $this->assertEquals('this is the second element', $this->getText('name=name1'));
        $this->assertFalse($this->isElementPresent('name=id1'));
        $this->assertFalse($this->isElementPresent('name=notAName'));
        $this->assertEquals('a2', $this->getAttribute('name=name1@class'));

        $this->assertEquals('this is the first element', $this->getText('identifier=id1'));
        $this->assertFalse($this->isElementPresent('identifier=id4'));
        $this->assertEquals('a1', $this->getAttribute('identifier=id1@class'));
        $this->assertEquals('this is the second element', $this->getText('identifier=name1'));
        $this->assertEquals('a2', $this->getAttribute('identifier=name1@class'));

        $this->assertEquals('this is the second element', $this->getText('dom=document.links[1]'));
        $this->assertEquals('a2', $this->getAttribute('dom=document.links[1]@class'));
        $this->assertFalse($this->isElementPresent('dom=document.links[9]'));
        $this->assertFalse($this->isElementPresent('dom=foo'));
    }

    public function testImplicitLocators()
    {
        $this->open($this->url . 'html/test_locators.html');
        $this->assertEquals('this is the first element', $this->getText('id1'));
        $this->assertEquals('a1', $this->getAttribute('id1@class'));

        $this->assertEquals('this is the second element', $this->getText('name1'));
        $this->assertEquals('a2', $this->getAttribute('name1@class'));

        $this->assertEquals('this is the second element', $this->getText('document.links[1]'));
        $this->assertEquals('a2', $this->getAttribute('document.links[1]@class'));

        $this->assertEquals('this is the second element', $this->getText('//body/a[2]'));
    }

    public function testXPathLocators()
    {
        $this->open($this->url . 'html/test_locators.html');
        $this->assertEquals('this is the first element', $this->getText('xpath=//a'));
        $this->assertEquals('this is the second element', $this->getText("xpath=//a[@class='a2']"));
        $this->assertEquals('this is the second element', $this->getText("xpath=//*[@class='a2']"));
        $this->assertEquals('this is the second element', $this->getText('xpath=//a[2]'));
        $this->assertFalse($this->isElementPresent("xpath=//a[@href='foo']"));

        $this->assertEquals('a1', $this->getAttribute("xpath=//a[contains(@href, '#id1')]/@class"));
        $this->assertTrue($this->isElementPresent("//a[text()='this is the second element']"));

        $this->assertEquals('this is the first element', $this->getText('xpath=//a'));
        $this->assertEquals('a1', $this->getAttribute("//a[contains(@href, '#id1')]/@class"));

        $this->assertEquals('theCellText', $this->getText("xpath=(//table[@class='stylee'])//th[text()='theHeaderText']/../td"));

        $this->click("//input[@name='name2' and @value='yes']");
    }

    public function testGoBack()
    {
        $this->open($this->url . 'html/test_click_page1.html');
        $this->assertEquals('Click Page 1', $this->getTitle());

        $this->click('link');
        $this->waitForPageToLoad(500);
        $this->assertEquals('Click Page Target', $this->getTitle());

        $this->goBack();
        $this->waitForPageToLoad(500);
        $this->assertEquals('Click Page 1', $this->getTitle());
    }

    public function testRefresh()
    {
        $this->open($this->url . 'html/test_page.slow.html');
        $this->assertStringEndsWith('html/test_page.slow.html', $this->getLocation());
        $this->assertEquals('Slow Loading Page', $this->getTitle());

        $this->click('changeSpan');
        $this->assertEquals('Changed the text', $this->getText('theSpan'));
        $this->refresh();
        $this->waitForPageToLoad(500);
        $this->assertNotEquals('Changed the text', $this->getText('theSpan'));

        $this->click('changeSpan');
        $this->assertEquals('Changed the text', $this->getText('theSpan'));
        $this->click('slowRefresh');
    }

    public function testLinkEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->assertEquals('', $this->getValue('eventlog'));
        $this->click('theLink');
        $this->assertEquals('{focus(theLink)} {click(theLink)}', $this->getValue('eventlog'));
        $this->assertEquals('link clicked', $this->getAlert());
        $this->click('theButton');
    }

    public function testButtonEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->assertEquals('', $this->getValue('eventlog'));
        $this->click('theButton');
        $this->assertEquals('{focus(theButton)} {click(theButton)}', $this->getValue('eventlog'));
        $this->type('eventlog', '');

        $this->click('theSubmit');
        $this->assertEquals('{focus(theSubmit)} {click(theSubmit)} {submit}', $this->getValue('eventlog'));

    }

    public function testSelectEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->assertEquals('', $this->getValue('theSelect'));
        $this->assertEquals('', $this->getValue('eventlog'));

        $this->select('theSelect', 'First Option');
        $this->assertEquals('option1', $this->getValue('theSelect'));
        $this->assertEquals('{focus(theSelect)} {change(theSelect)}', $this->getValue('eventlog'));

        $this->type('eventlog', '');
        $this->select('theSelect', 'First Option');
        $this->assertEquals('option1', $this->getValue('theSelect'));
        $this->assertEquals('{focus(theSelect)}', $this->getValue('eventlog'));

        $this->type('eventlog', '');
        $this->select('theSelect', 'Empty Option');
        $this->assertEquals('', $this->getValue('theSelect'));
        $this->assertEquals('{focus(theSelect)} {change(theSelect)}', $this->getValue('eventlog'));

    }

    public function testRadioEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->assertEquals('off', $this->getValue('theRadio1'));
        $this->assertEquals('off', $this->getValue('theRadio2'));
        $this->assertEquals('', $this->getValue('eventlog'));

        $this->click('theRadio1');
        $this->assertEquals('on', $this->getValue('theRadio1'));
        $this->assertEquals('off', $this->getValue('theRadio2'));
        $this->assertEquals('{focus(theRadio1)} {click(theRadio1)} {change(theRadio1)}', $this->getValue('eventlog'));

        $this->type('eventlog', '');
        $this->click('theRadio2');
        $this->assertEquals('off', $this->getValue('theRadio1'));
        $this->assertEquals('on', $this->getValue('theRadio2'));
        $this->assertEquals('{focus(theRadio2)} {click(theRadio2)} {change(theRadio2)}', $this->getValue('eventlog'));


        $this->type('eventlog', '');
        $this->click('theRadio2');
        $this->assertEquals('off', $this->getValue('theRadio1'));
        $this->assertEquals('on', $this->getValue('theRadio2'));
        $this->assertEquals('{focus(theRadio2)} {click(theRadio2)}', $this->getValue('eventlog'));
    }

    public function testCheckboxEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->assertEquals('off', $this->getValue('theCheckbox'));
        $this->assertEquals('', $this->getValue('eventlog'));

        $this->click('theCheckbox');
        $this->assertEquals('on', $this->getValue('theCheckbox'));
        $this->assertEquals('{focus(theCheckbox)} {click(theCheckbox)} {change(theCheckbox)}', $this->getValue('eventlog'));

        $this->type('eventlog', '');
        $this->click('theCheckbox');
        $this->assertEquals('off', $this->getValue('theCheckbox'));
        $this->assertEquals('{focus(theCheckbox)} {click(theCheckbox)} {change(theCheckbox)}', $this->getValue('eventlog'));
    }

    public function testTextEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->assertEquals('', $this->getValue('theTextbox'));
        $this->assertEquals('', $this->getValue('eventlog'));

        $this->type('theTextbox', 'first value');
        $this->assertEquals('first value', $this->getValue('theTextbox'));
        $this->assertEquals('{focus(theTextbox)} {select(theTextbox)} {change(theTextbox)}', $this->getValue('eventlog'));

        $this->type('eventlog', '');
        $this->type('theTextbox', 'changed value');
        $this->assertEquals('changed value', $this->getValue('theTextbox'));
        $this->assertEquals('{focus(theTextbox)} {select(theTextbox)} {change(theTextbox)}', $this->getValue('eventlog'));
    }

    public function testFireEvents()
    {
        $this->open($this->url .'html/test_form_events.html');
        $this->assertEquals('', $this->getValue('eventlog'));
        $this->fireEvent('theTextbox', 'focus');
        $this->assertEquals('{focus(theTextbox)}', $this->getValue('eventlog'));

        $this->type('eventlog', '');
        $this->fireEvent('theSelect', 'change');
        $this->fireEvent('theSelect', 'blur');
        $this->assertEquals('{change(theSelect)} {blur(theSelect)}', $this->getValue('eventlog'));

        $this->type('theTextbox', 'changed value');
    }

    public function testMouseEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->mouseOver('theTextbox');
        $this->mouseOver('theButton');
        $this->mouseDown('theTextbox');
        $this->mouseDown('theButton');
        $this->assertEquals('{mouseover(theTextbox)} {mouseover(theButton)} {mousedown(theTextbox)} {mousedown(theButton)}', $this->getValue('eventlog'));
    }

    public function testKeyEvents()
    {
        $this->open($this->url . 'html/test_form_events.html');
        $this->keyPress('theTextbox', '119');
        $this->keyPress('theTextbox', '115');
        $this->keyUp('theTextbox', '44');
        $this->keyDown('theTextbox', '98');
        $this->assertEquals('{keypress(theTextbox - 119)} {keypress(theTextbox - 115)} {keyup(theTextbox - 44)} {keydown(theTextbox - 98)}', $this->getValue('eventlog'));
    }

    public function testFocusOnBlur()
    {
        $this->open($this->url . 'html/test_focus_on_blur.html');
        $this->type('testInput', 'test');
        $this->fireEvent('testInput', 'blur');
        $this->assertEquals('Bad value', $this->getAlert());
        $this->type('testInput', 'somethingelse');
    }

    public function testAlerts()
    {
        $this->open($this->url . 'html/test_verify_alert.html');
        $this->assertFalse($this->isAlertPresent());

        $this->click('oneAlert');
        $this->assertTrue($this->isAlertPresent());
        $this->assertEquals('Store Below 494 degrees K!', $this->getAlert());

        $this->click('twoAlerts');
        $this->assertEquals('Store Below 220 degrees C!', $this->getAlert());
        $this->assertEquals('Store Below 429 degrees F!', $this->getAlert());
        $this->click('alertAndLeave');
        $this->waitForPageToLoad(500);
        $this->assertEquals("I'm Melting! I'm Melting!", $this->getAlert());
    }

    public function testConfirmations()
    {
        $this->open($this->url . 'html/test_confirm.html');
        $this->chooseCancelOnNextConfirmation();
        $this->click('confirmAndLeave');
        $this->assertTrue($this->isConfirmationPresent());
        $this->assertEquals('You are about to go to a dummy page.', $this->getConfirmation());
        $this->assertEquals('Test Confirm', $this->getTitle());

        $this->click('confirmAndLeave');
        $this->waitForPageToLoad(500);
        $this->assertEquals('You are about to go to a dummy page.', $this->getConfirmation());
        $this->assertEquals('Dummy Page', $this->getTitle());
    }

    public function testPrompt()
    {
        $this->open($this->url . 'html/test_prompt.html');
        $this->assertFalse($this->isPromptPresent());

        $this->click('promptAndLeave');
        $this->assertTrue($this->isPromptPresent());
        $this->assertEquals("Type 'yes' and click OK", $this->getPrompt());
        $this->assertEquals('Test Prompt', $this->getTitle());
        $this->answerOnNextPrompt('yes');
        $this->click('promptAndLeave');

        $this->waitForPageToLoad(500);
        $this->assertEquals("Type 'yes' and click OK", $this->getPrompt());
        $this->assertEquals('Dummy Page', $this->getTitle());
    }

    public function testVisibility()
    {
        $this->open($this->url . 'html/test_visibility.html');
        $this->assertTrue($this->isVisible('visibleParagraph'));
        $this->assertFalse($this->isVisible('hiddenParagraph'));
        $this->assertFalse($this->isVisible('suppressedParagraph'));
        $this->assertFalse($this->isVisible('classSuppressedParagraph'));
        $this->assertFalse($this->isVisible('jsClassSuppressedParagraph'));
        $this->assertFalse($this->isVisible('hiddenSubElement'));
        $this->assertTrue($this->isVisible('visibleSubElement'));
        $this->assertFalse($this->isVisible('suppressedSubElement'));
        $this->assertFalse($this->isVisible('jsHiddenParagraph'));
    }

    public function testEditable()
    {
        $this->open($this->url . 'html/test_editable.html');
        $this->assertTrue($this->isEditable('normal_text'));
        $this->assertTrue($this->isEditable('normal_select'));
        $this->assertFalse($this->isEditable('disabled_text'));
        $this->assertFalse($this->isEditable('disabled_select'));
    }
}
?>