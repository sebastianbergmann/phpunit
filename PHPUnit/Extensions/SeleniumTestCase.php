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
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'Testing/Selenium.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * TestCase class that uses Selenium to provide
 * the functionality required for web testing.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Extensions_SeleniumTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Testing_Selenium
     * @access private
     */
    private $selenium = NULL;

    /**
     * @var    string
     * @access private
     */
    private $browser;

    /**
     * @var    string
     * @access private
     */
    private $browserUrl;

    /**
     * @var    string
     * @access private
     */
    private $host = 'localhost';

    /**
     * @var    integer
     * @access private
     */
    private $port = 4444;

    /**
     * @var    integer
     * @access private
     */
    private $timeout = 30000;

    /**
     * @access protected
     */
    protected function runTest()
    {
        if (extension_loaded('curl')) {
            $driver = 'curl';
        } else {
            $driver = 'native';
        }

        $this->selenium = new Testing_Selenium(
          $this->browser,
          $this->browserUrl,
          $this->host,
          $this->port,
          $this->timeout,
          $driver
        );

        $this->start();

        parent::runTest();

        try {
            $this->stop();
        }

        catch (Selenium_Exception $e) {
        }

        $this->selenium = NULL;
    }

    protected function tearDown()
    {
        try {
            $this->stop();
        }

        catch (Selenium_Exception $e) {
        }

        $this->selenium = NULL;
    }

    /**
     * @param  string  $browser
     * @throws InvalidArgumentException
     * @access public
     */
    public function setBrowser($browser)
    {
        if (!is_string($browser)) {
            throw new InvalidArgumentException;
        }

        $this->browser = $browser;
    }

    /**
     * @param  string  $browserUrl
     * @throws InvalidArgumentException
     * @access public
     */
    public function setBrowserUrl($browserUrl)
    {
        if (!is_string($browserUrl)) {
            throw new InvalidArgumentException;
        }

        $this->browserUrl = $browserUrl;
    }

    /**
     * @param  string  $host
     * @throws InvalidArgumentException
     * @access public
     */
    public function setHost($host)
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException;
        }

        $this->host = $host;
    }

    /**
     * @param  integer  $port
     * @throws InvalidArgumentException
     * @access public
     */
    public function setPort($port)
    {
        if (!is_int($port)) {
            throw new InvalidArgumentException;
        }

        $this->port = $port;
    }

    /**
     * @param  integer  $timeout
     * @throws InvalidArgumentException
     * @access public
     */
    public function setTimeout($timeout)
    {
        if (!is_int($timeout)) {
            throw new InvalidArgumentException;
        }

        $this->timeout = $timeout;
    }

    /**
     * Asserts that an alert is present.
     *
     * @access public
     */
    public function assertAlertPresent()
    {
        $this->assertTrue($this->isAlertPresent());
    }

    /**
     * Asserts that no alert is present.
     *
     * @access public
     */
    public function assertNoAlertPresent()
    {
        $this->assertFalse($this->isAlertPresent());
    }

    /**
     * 
     *
     * @param  string  $locator
     * @access public
     */
    public function assertChecked($locator)
    {
        $this->assertTrue($this->isChecked($locator));
    }

    /**
     * 
     *
     * @param  string  $locator
     * @access public
     */
    public function assertNotChecked($locator)
    {
        $this->assertFalse($this->isChecked($locator));
    }

    /**
     * Assert that a confirmation is present.
     *
     * @access public
     */
    public function assertConfirmationPresent()
    {
        $this->assertTrue($this->isConfirmationPresent());
    }

    /**
     * Assert that no confirmation is present.
     *
     * @access public
     */
    public function assertNoConfirmationPresent()
    {
        $this->assertFalse($this->isConfirmationPresent());
    }

    /**
     * 
     *
     * @param  string  $locator
     * @access public
     */
    public function assertEditable($locator)
    {
        $this->assertTrue($this->isEditable($locator));
    }

    /**
     * 
     *
     * @param  string  $locator
     * @access public
     */
    public function assertNotEditable($locator)
    {
        $this->assertFalse($this->isEditable($locator));
    }

    /**
     * Asserts than an element is present.
     *
     * @param  string  $locator
     * @access public
     */
    public function assertElementPresent($locator)
    {
        $this->assertTrue($this->isElementPresent($locator));
    }

    /**
     * Asserts than an element is not present.
     *
     * @param  string  $locator
     * @access public
     */
    public function assertElementNotPresent($locator)
    {
        $this->assertFalse($this->isElementPresent($locator));
    }

    /**
     * 
     *
     * @param  string  $location
     * @access public
     */
    public function assertLocationEquals($location)
    {
        $this->assertEquals($location, $this->getLocation());
    }

    /**
     * 
     *
     * @param  string  $location
     * @access public
     */
    public function assertLocationNotEquals($location)
    {
        $this->assertNotEquals($location, $this->getLocation());
    }

    /**
     * Asserts than a prompt is present.
     *
     * @access public
     */
    public function assertPromptPresent()
    {
        $this->assertTrue($this->isPromptPresent());
    }

    /**
     * Asserts than no prompt is present.
     *
     * @access public
     */
    public function assertNoPromptPresent()
    {
        $this->assertFalse($this->isPromptPresent());
    }

    /**
     * Asserts that something is selected.
     *
     * @param  string  $selectLocator
     * @access public
     */
    public function assertSomethingSelected($selectLocator)
    {
        $this->assertTrue($this->isSomethingSelected($selectLocator));
    }

    /**
     * Asserts that nothing is selected.
     *
     * @param  string  $selectLocator
     * @access public
     */
    public function assertNothingSelected($selectLocator)
    {
        $this->assertFalse($this->isSomethingSelected($selectLocator));
    }

    /**
     * Asserts that a given text is present.
     *
     * @param  string  $pattern
     * @access public
     */
    public function assertTextPresent($pattern)
    {
        $this->assertTrue($this->isTextPresent($pattern));
    }

    /**
     * Asserts that a given text is not present.
     *
     * @param  string  $pattern
     * @access public
     */
    public function assertTextNotPresent($pattern)
    {
        $this->assertFalse($this->isTextPresent($pattern));
    }

    /**
     * Asserts that the title is equal to a given string.
     *
     * @param  string  $title
     * @access public
     */
    public function assertTitleEquals($title)
    {
        $this->assertEquals($title, $this->getTitle());
    }

    /**
     * Asserts that the title is not equal to a given string.
     *
     * @param  string  $title
     * @access public
     */
    public function assertTitleNotEquals($title)
    {
        $this->assertNotEquals($title, $this->getTitle());
    }

    /**
     * Asserts that something is visible.
     *
     * @param  string  $locator
     * @access public
     */
    public function assertVisible($locator)
    {
        $this->assertTrue($this->isVisible($locator));
    }


    /**
     * Asserts that something is not visible.
     *
     * @param  string  $locator
     * @access public
     */
    public function assertNotVisible($locator)
    {
        $this->assertFalse($this->isVisible($locator));
    }

    /**
     * Retrieves the message of a JavaScript alert generated during
     * the previous action
     *
     * @return string
     * @access public
     */
    public function getAlert()
    {
        return $this->selenium->getAlert();
    }

    /**
     * Returns the IDs of all buttons on the page.
     *
     * @return array
     * @access public
     */
    public function getAllButtons()
    {
        return $this->selenium->getAllButtons();
    }

    /**
     * Returns the IDs of all input fields on the page.
     *
     * @return array
     * @access public
     */
    public function getAllFields()
    {
        return $this->selenium->getAllFields();
    }

    /**
     * Returns the IDs of all links on the page.
     *
     * @return array
     * @access public
     */
    public function getAllLinks()
    {
        return $this->selenium->getAllLinks();
    }

    /**
     * Gets the entire text of the page.
     *
     * @return string
     * @access public
     */
    public function getBodyText()
    {
        return $this->selenium->getBodyText();
    }

    /**
     * Retrieves the message of a JavaScript confirmation dialog
     * generated during the previous action.
     *
     * @return string
     * @access public
     */
    public function getConfirmation()
    {
        return $this->selenium->getConfirmation();
    }

    /**
     * Retrieves the text cursor position in the given input element
     * or textarea.
     *
     * @param  string  $locator
     * @return integer
     * @access public
     */
    public function getCursorPosition($locator)
    {
        return $this->selenium->getCursorPosition($locator);
    }

    /**
     * Moves the text cursor to the specified position in the given
     * input element or textarea.
     *
     * @param  string  $locator
     * @param  integer $position
     * @return string
     * @access public
     */
    public function setCursorPosition($locator, $position)
    {
        return $this->selenium->setCursorPosition($locator, $position);
    }

    /**
     * Gets the value of an element attribute.
     *
     * Note: This method should be named getAttribute(), but that
     *       method is already defined in PHPUnit_Framework_Assert.
     *
     * @param  string  $attributeLocator
     * @return string
     * @access public
     */
    public function getElementAttribute($attributeLocator)
    {
        return $this->selenium->getAttribute($attributeLocator);
    }

    /**
     * Gets the result of evaluating the specified JavaScript snippet.
     *
     * @param  string  $script
     * @return string
     * @access public
     */
    public function getEval($script)
    {
        return $this->selenium->getEval($script);
    }

    /**
     * Returns the specified expression.
     *
     * @param  string  $expression
     * @return string
     * @access public
     */
    public function getExpression($expression)
    {
        return $this->selenium->getExpression($expression);
    }

    /**
     * Returns the entire HTML source between the opening and
     * closing "html" tags.
     *
     * @return string
     * @access public
     */
    public function getHtmlSource()
    {
        return $this->selenium->getHtmlSource();
    }

    /**
     * Gets the absolute URL of the current page.
     *
     * @return string
     * @access public
     */
    public function getLocation()
    {
        return $this->selenium->getLocation();
    }

    /**
     * Retrieves the message of a JavaScript question prompt dialog
     * generated during the previous action.
     *
     * @return string
     * @access public
     */
    public function getPrompt()
    {
        return $this->selenium->getPrompt();
    }

    /**
     * Gets option element ID for selected option in the specified
     * select element.
     *
     * @param  string  $selectLocator
     * @return string
     * @access public
     */
    public function getSelectedId($selectLocator)
    {
        return $this->selenium->getSelectedId($selectLocator);
    }

    /**
     * Gets all option element IDs for selected options in the specified
     * select or multi-select element.
     *
     * @param  string  $selectLocator
     * @return array
     * @access public
     */
    public function getSelectedIds($selectLocator)
    {
        return $this->selenium->getSelectedIds($selectLocator);
    }

    /**
     * Gets option index (option number, starting at 0) for selected
     * option in the specified select element.
     *
     * @param  string  $selectLocator
     * @return string
     * @access public
     */
    public function getSelectedIndex($selectLocator)
    {
        return $this->selenium->getSelectedIndex($selectLocator);
    }

    /**
     * Gets all option indexes (option number, starting at 0) for selected
     * options in the specified select or multi-select element.
     *
     * @param  string  $selectLocator
     * @return array
     * @access public
     */
    public function getSelectedIndexes($selectLocator)
    {
        return $this->selenium->getSelectedIndexes($selectLocator);
    }

    /**
     * Gets all option labels (visible text) for selected options
     * in the specified selector multi-select element.
     *
     * @param  string  $selectLocator
     * @return string
     * @access public
     */
    public function getSelectedLabel($selectLocator)
    {
        return $this->selenium->getSelectedLabel($selectLocator);
    }

    /**
     * Gets all option labels (visible text) for selected options
     * in the specified select or multi-select element.
     *
     * @param  string  $selectLocator
     * @return array
     * @access public
     */
    public function getSelectedLabels($selectLocator)
    {
        return $this->selenium->getSelectedLabels($selectLocator);
    }

    /**
     * Gets option value (value attribute) for selected option
     * in the specified select element.
     *
     * @param  string  $selectLocator
     * @return string
     * @access public
     */
    public function getSelectedValue($selectLocator)
    {
        return $this->selenium->getSelectedValue($selectLocator);
    }

    /**
     * Gets all option values (value attributes) for selected options
     * in the specified select or multi-select element.
     *
     * @param  string  $selectLocator
     * @return array
     * @access public
     */
    public function getSelectedValues($selectLocator)
    {
        return $this->selenium->getSelectedValues($selectLocator);
    }

    /**
     * Gets all option labels in the specified select drop-down.
     *
     * @param  string  $selectLocator
     * @return array
     * @access public
     */
    public function getSelectOptions($selectLocator)
    {
        return $this->selenium->getSelectOptions($selectLocator);
    }

    /**
     * Gets the text from a cell of a table.
     *
     * @param  string  $tableCellAddress
     * @return string
     * @access public
     */
    public function getTable($tableCellAddress)
    {
        return $this->selenium->getTable($tableCellAddress);
    }

    /**
     * Gets the text of an element.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function getText($locator)
    {
        return $this->selenium->getText($locator);
    }

    /**
     * Gets the title of the current page.
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return $this->selenium->getTitle();
    }

    /**
     * Gets the (whitespace-trimmed) value of an input field
     * (or anything else with a value parameter).
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function getValue($locator)
    {
        return $this->selenium->getValue($locator);
    }

    /**
     * Has an alert occured?
     *
     * @return boolean
     * @access public
     */
    public function isAlertPresent()
    {
        return $this->selenium->isAlertPresent();
    }

    /**
     * Determines whether a toggle-button (checkbox/radio) is checked.
     *
     * @param  string  $locator
     * @return boolean
     * @access public
     */
    public function isChecked($locator)
    {
        return $this->selenium->isChecked($locator);
    }

    /**
     * Has confirm() been called?
     *
     * @return boolean
     * @access public
     */
    public function isConfirmationPresent()
    {
        return $this->selenium->isConfirmationPresent();
    }

    /**
     * Determines whether the specified input element is editable.
     *
     * @param  string  $locator
     * @return boolean
     * @access public
     */
    public function isEditable($locator)
    {
        return $this->selenium->isEditable($locator);
    }

    /**
     * Verifies that the specified element is somewhere on the page.
     *
     * @param  string  $locator
     * @return boolean
     * @access public
     */
    public function isElementPresent($locator)
    {
        return $this->selenium->isElementPresent($locator);
    }

    /**
     * Has a prompt occured?
     *
     * @return boolean
     * @access public
     */
    public function isPromptPresent()
    {
        return $this->selenium->isPromptPresent();
    }

    /**
     * Determines whether some option in a drop-down menu is selected.
     *
     * @param  string  $selectLocator
     * @return boolean
     * @access public
     */
    public function isSomethingSelected($selectLocator)
    {
        return $this->selenium->isSomethingSelected($selectLocator);
    }

    /**
     * Verifies that the specified text pattern appears somewhere
     * on the rendered page shown to the user.
     *
     * @param  string  $pattern
     * @return boolean
     * @access public
     */
    public function isTextPresent($pattern)
    {
        return $this->selenium->isTextPresent($pattern);
    }

    /**
     * Determines if the specified element is visible.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function isVisible($locator)
    {
        return $this->selenium->isVisible($locator);
    }

    /**
     * Run the browser and set session id.
     *
     * @return string
     * @access public
     */
    public function start()
    {
        return $this->selenium->start();
    }

    /**
     * Close the browser and set session to NULL.
     *
     * @return string
     * @access public
     */
    public function stop()
    {
        if ($this->selenium !== NULL) {
            return $this->selenium->stop();
        }
    }

    /**
     * Open the URL in the test frame.
     *
     * @param  string  $url
     * @return string
     * @access public
     */
    public function open($url)
    {
        return $this->selenium->open($url);
    }

    /**
     * Simulates the user clicking the "close" button" in the titlebar
     * of a popup window or tab.
     *
     * @return string
     * @access public
     */
    public function close()
    {
        return $this->selenium->close();
    }

    /**
     * Simulates clicking on the browser's "go back" button.
     *
     * @return string
     * @access public
     */
    public function goBack()
    {
        return $this->selenium->goBack();
    }

    /**
     * Simulates clicking on the browser's "refresh" button.
     *
     * @return string
     * @access public
     */
    public function refresh()
    {
        return $this->selenium->refresh();
    }

    /**
     * Clicks on a link, button, checkbox or radio button. 
     * If the click action causes a new page to load,
     * call waitForPageToLoad.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function click($locator)
    {
        return $this->selenium->click($locator);
    }

    /**
     * Simulate an event to trigger the corresponding "onEvent" handler.
     *
     * @param  string  $locator
     * @param  string  $eventName
     * @return string
     * @access public
     */
    public function fireEvent($locator, $eventName)
    {
        return $this->selenium->fireEvent($locator, $eventName);
    }

    /**
     * Simulates a user pressing and holding a key.
     *
     * @param  string  $locator
     * @param  string  $keycode
     * @return string
     * @access public
     */
    public function keyDown($locator, $keycode)
    {
        return $this->selenium->keyDown($locator, $keycode);
    }

    /**
     * Simulates a user pressing and releasing a key.
     *
     * @param  string  $locator
     * @param  string  $keycode
     * @return string
     * @access public
     */
    public function keyPress($locator, $keycode)
    {
        return $this->selenium->keyPress($locator, $keycode);
    }

    /**
     * Simulates a user releasing a key.
     *
     * @param  string  $locator
     * @param  string  $keycode
     * @return string
     * @access public
     */
    public function keyUp($locator, $keycode)
    {
        return $this->selenium->keyUp($locator, $keycode);
    }

    /**
     * Simulates a user pressing and holding the mouse button on
     * the specified element.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function mouseDown($locator)
    {
        return $this->selenium->mouseDown($locator);
    }

    /**
     * Simulates a user hovering a mouse over the specified element.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function mouseOver($locator)
    {
        return $this->selenium->mouseOver($locator);
    }

    /**
     * Check a toggle-button (checkbox/radio).
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function check($locator)
    {
        return $this->selenium->check($locator);
    }

    /**
     * Uncheck a toggle-button (checkbox/radio).
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function uncheck($locator)
    {
        return $this->selenium->uncheck($locator);
    }

    /**
     * Add a selection to the set of selected options in a multi-select
     * element using an option locator.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function addSelection($locator, $optionLocator)
    {
        return $this->selenium->addSelection($locator, $optionLocator);
    }

    /**
     * Remove a selection to the set of selected options in a multi-select
     * element using an option locator.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function removeSelection($locator, $optionLocator)
    {
        return $this->selenium->removeSelection($locator, $optionLocator);
    }

    /**
     * Select an option from a drop-down using an option locator.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function select($selectLocator, $optionLocator)
    {
        return $this->selenium->select($selectLocator, $optionLocator);
    }

    /**
     * Submit the specified form.
     *
     * @param  string  $locator
     * @return string
     * @access public
     */
    public function submit($locator)
    {
        return $this->selenium->submit($locator);
    }

    /**
     * Type into an input field.
     *
     * @param  string  $locator
     * @param  string  $value
     * @return string
     * @access public
     */
    public function type($locator, $value)
    {
        return $this->selenium->type($locator, $value);
    }

    /**
     * Selects a popup window; once a popup window has been selected,
     * all commands go to that window. To select the main window again,
     * use "null" as the target.
     *
     * @param  string  $windowId
     * @return string
     * @access public
     */
    public function selectWindow($windowId)
    {
        return $this->selenium->selectWindow($windowId);
    }

    /**
     * Writes a message to the status bar and adds a note
     * to the browser-side log.
     *
     * @param  string  $context
     * @param  string  $logLevelThreshold
     * @return string
     * @access public
     */
    public function setContext($context, $logLevelThreshold)
    {
        return $this->selenium->setContext($context, $logLevelThreshold);
    }

    /**
     * Instructs Selenium to return the specified answer string
     * in response to the next JavaScript prompt [window.prompt()].
     *
     * @param  string  $answer
     * @return string
     * @access public
     */
    public function answerOnNextPrompt($answer)
    {
        return $this->selenium->answerOnNextPrompt($answer);
    }

    /**
     * By default, Selenium's overridden window.confirm() function will
     * return true, as if the user had manually clicked OK.  After running
     * this command, the next call to confirm() will return false, as if
     * the user had clicked Cancel.
     *
     * @return string
     * @access public
     */
    public function chooseCancelOnNextConfirmation()
    {
        return $this->selenium->chooseCancelOnNextConfirmation();
    }

    /**
     * Runs the specified JavaScript snippet repeatedly 
     * until it evaluates to "true".
     *
     * @param  string  $script
     * @param  integer $timeout
     * @return string
     * @access public
     */
    public function waitForCondition($script, $timeout = NULL)
    {
        return $this->selenium->waitForCondition($script, $timeout);
    }

    /**
     * Waits for a new page to load.
     *
     * @param  integer  $timeout
     * @return string
     * @access public
     */
    public function waitForPageToLoad($timeout = NULL)
    {
        return $this->selenium->waitForPageToLoad($timeout);
    }

    /**
     * Wait for a popup window to appear and load up.
     *
     * @param  string  $windowId
     * @param  integer $timeout
     * @return 
     * @access public
     */
    public function waitForPopUp($windowId, $timeout = NULL)
    {
        return $this->selenium->waitForPopUp($windowId, $timeout);
    }
}
?>
