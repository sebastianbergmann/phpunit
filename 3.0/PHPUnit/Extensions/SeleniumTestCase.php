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
require_once 'Selenium.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Abstract TestCase class that uses Selenium to provide
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
     * @var    Selenium
     * @access private
     */
    private $selenium;

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
     * @param  integet  $port
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

    public function click($locator)
    {
        return $this->selenium->click($locator);
    }

    public function fireEvent($locator, $eventName)
    {
        return $this->selenium->fireEvent($locator, $eventName);
    }

    public function keyPress($locator, $keycode)
    {
        return $this->selenium->keyPress($locator, $keycode);
    }

    public function keyDown($locator, $keycode)
    {
        return $this->selenium->keyDown($locator, $keycode);
    }

    public function keyUp($locator, $keycode)
    {
        return $this->selenium->keyUp($locator, $keycode);
    }

    public function mouseOver($locator)
    {
        return $this->selenium->mouseOver($locator);
    }

    public function mouseDown($locator)
    {
        return $this->selenium->mouseDown($locator);
    }

    public function type($locator, $value)
    {
        return $this->selenium->type($locator, $value);
    }

    public function check($locator)
    {
        return $this->selenium->check($locator);
    }

    public function uncheck($locator)
    {
        return $this->selenium->uncheck($locator);
    }

    public function select($selectLocator, $optionLocator)
    {
        return $this->selenium->select($selectLocator, $optionLocator);
    }

    public function addSelection($locator, $optionLocator)
    {
        return $this->selenium->addSelection($locator, $optionLocator);
    }

    public function removeSelection($locator, $optionLocator)
    {
        return $this->selenium->removeSelection($locator, $optionLocator);
    }

    public function submit($locator)
    {
        return $this->selenium->submit($locator);
    }

    public function open($url)
    {
        return $this->selenium->open($url);
    }

    public function selectWindow($windowId)
    {
        return $this->selenium->selectWindow($windowId);
    }

    public function waitForPopUp($windowId, $timeout = NULL)
    {
        return $this->selenium->waitForPopUp($windowId, $timeout);
    }

    public function chooseCancelOnNextConfirmation()
    {
        return $this->selenium->chooseCancelOnNextConfirmation();
    }

    public function answerOnNextPrompt($answer)
    {
        return $this->selenium->answerOnNextPrompt($answer);
    }

    public function goBack()
    {
        return $this->selenium->goBack();
    }

    public function refresh()
    {
        return $this->selenium->refresh();
    }

    public function close()
    {
        return $this->selenium->close();
    }

    public function isAlertPresent()
    {
        return $this->selenium->isAlertPresent();
    }

    public function getAlert()
    {
        return $this->selenium->getAlert();
    }

    public function isPromptPresent()
    {
        return $this->selenium->isPromptPresent();
    }

    public function getPrompt()
    {
        return $this->selenium->getPrompt();
    }

    public function isConfirmationPresent()
    {
        return $this->selenium->isConfirmationPresent();
    }

    public function getConfirmation()
    {
        return $this->selenium->getConfirmation();
    }

    public function getLocation()
    {
        return $this->selenium->getLocation();
    }

    public function getTitle()
    {
        return $this->selenium->getTitle();
    }

    public function getBodyText()
    {
        return $this->selenium->getBodyText();
    }

    public function getValue($locator)
    {
        return $this->selenium->getValue($locator);
    }

    public function getText($locator)
    {
        return $this->selenium->getText($locator);
    }

    public function getEval($script)
    {
        return $this->selenium->getEval($script);
    }

    public function isChecked($locator)
    {
        return $this->selenium->isChecked($locator);
    }

    public function getTable($tableCellAddress)
    {
        return $this->selenium->getTable($tableCellAddress);
    }

    public function getSelectedLabels($selectLocator)
    {
        return $this->selenium->getSelectedLabels($selectLocator);
    }

    public function getSelectedLabel($selectLocator)
    {
        return $this->selenium->getSelectedLabel($selectLocator);
    }

    public function getSelectedValues($selectLocator)
    {
        return $this->selenium->getSelectedValues($selectLocator);
    }

    public function getSelectedValue($selectLocator)
    {
        return $this->selenium->getSelectedValue($selectLocator);
    }

    public function getSelectedIndexes($selectLocator)
    {
        return $this->selenium->getSelectedIndexes($selectLocator);
    }

    public function getSelectedIndex($selectLocator)
    {
        return $this->selenium->getSelectedIndex($selectLocator);
    }

    public function getSelectedIds($selectLocator)
    {
        return $this->selenium->getSelectedIds($selectLocator);
    }

    public function getSelectedId($selectLocator)
    {
        return $this->selenium->getSelectedId($selectLocator);
    }

    public function isSomethingSelected($selectLocator)
    {
        return $this->selenium->isSomethingSelected($selectLocator);
    }

    public function getSelectOptions($selectLocator)
    {
        return $this->selenium->getSelectOptions($selectLocator);
    }

    public function getElementAttribute($attributeLocator)
    {
        return $this->selenium->getAttribute($attributeLocator);
    }

    public function isTextPattern($pattern)
    {
        return $this->selenium->isTextPattern($pattern);
    }

    public function isElementPresent($locator)
    {
        return $this->selenium->isElementPresent($locator);
    }

    public function isVisible($locator)
    {
        return $this->selenium->isVisible($locator);
    }

    public function isEditable($locator)
    {
        return $this->selenium->isEditable($locator);
    }

    public function getAllButtons()
    {
        return $this->selenium->getAllButtons();
    }

    public function getAllLinks()
    {
        return $this->selenium->getAllLinks();
    }

    public function getAllFields()
    {
        return $this->selenium->getAllFields();
    }

    public function getHtmlSource()
    {
        return $this->selenium->getHtmlSource();
    }

    public function setCursorPosition($locator, $position)
    {
        return $this->selenium->setCursorPosition($locator, $position);
    }

    public function getCursorPosition($locator)
    {
        return $this->selenium->getCursorPosition($locator);
    }

    public function setContext($context, $logLevelThreshold)
    {
        return $this->selenium->setContext($context, $logLevelThreshold);
    }

    public function getExpression($expression)
    {
        return $this->selenium->getExpression($expression);
    }

    public function waitForCondition($script, $timeout = NULL)
    {
        return $this->selenium->waitForCondition($script, $timeout);
    }

    public function waitForPageToLoad($timeout = NULL)
    {
        return $this->selenium->waitForPageToLoad($timeout);
    }

    public function assertTitleEquals($title)
    {
        $this->assertEquals($title, $this->getTitle());
    }

    public function assertTitleNotEquals($title)
    {
        $this->assertNotEquals($title, $this->getTitle());
    }

    public function assertAlertPresent()
    {
        $this->assertTrue($this->isAlertPresent());
    }

    public function assertAlertNotPresent()
    {
        $this->assertFalse($this->isAlertPresent());
    }

    public function assertPromptPresent()
    {
        $this->assertTrue($this->isPromptPresent());
    }

    public function assertPromptNotPresent()
    {
        $this->assertFalse($this->isPromptPresent());
    }

    public function assertConfirmationPresent()
    {
        $this->assertTrue($this->isConfirmationPresent());
    }

    public function assertConfirmationNotPresent()
    {
        $this->assertFalse($this->isConfirmationPresent());
    }

    public function assertChecked($locator)
    {
        $this->assertTrue($this->isChecked($locator));
    }

    public function assertNotChecked($locator)
    {
        $this->assertFalse($this->isChecked($locator));
    }

    public function assertSomethingSelected($selectLocator)
    {
        $this->assertTrue($this->isSomethingSelected($selectLocator));
    }

    public function assertNothingSelected($selectLocator)
    {
        $this->assertFalse($this->isSomethingSelected($selectLocator));
    }

    public function assertElementPresent($locator)
    {
        $this->assertTrue($this->isElementPresent($locator));
    }

    public function assertElementNotPresent($locator)
    {
        $this->assertFalse($this->isElementPresent($locator));
    }

    public function assertVisible($locator)
    {
        $this->assertTrue($this->isVisible($locator));
    }

    public function assertNotVisible($locator)
    {
        $this->assertFalse($this->isVisible($locator));
    }

    public function assertEditable($locator)
    {
        $this->assertTrue($this->isEditable($locator));
    }

    public function assertNotEditable($locator)
    {
        $this->assertFalse($this->isEditable($locator));
    }

    /**
     * @param  string  $expectedString
     * @access public
     */
    protected function runTest()
    {
        if (extension_loaded('curl')) {
            $driver = 'curl';
        } else {
            $driver = 'native';
        }

        $this->selenium = new Selenium(
          $this->browser,
          $this->browserUrl,
          $this->host,
          $this->port,
          $this->timeout,
          $driver
        );

        $this->selenium->start();

        parent::runTest();

        $this->selenium->stop();
    }
}
?>
