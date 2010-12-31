<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Extensions
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * A TestCase that expects a specified output.
 *
 * @package    PHPUnit
 * @subpackage Extensions
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
abstract class PHPUnit_Extensions_OutputTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var    string
     */
    protected $expectedRegex = NULL;

    /**
     * @var    string
     */
    protected $expectedString = NULL;

    /**
     * @var    string
     */
    protected $output = '';

    /**
     * @var    boolean
     */
    protected $obActive = FALSE;

    /**
     * @var    mixed
     */
    protected $outputCallback = FALSE;

    /**
     * @return bool
     */
    public function setOutputCallback($callback)
    {
        if (is_callable($callback)) {
            $this->outputCallback = $callback;
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @return string
     */
    public function normalizeOutput($buffer)
    {
        return str_replace("\r", '', $buffer);
    }

    /**
     * @return string
     */
    public function getActualOutput()
    {
        if (!$this->obActive) {
            return $this->output;
        } else {
            return ob_get_contents();
        }
    }

    /**
     * @return string
     */
    public function expectedRegex()
    {
        return $this->expectedRegex;
    }

    /**
     * @param  string  $expectedRegex
     */
    public function expectOutputRegex($expectedRegex)
    {
        if ($this->expectedString !== NULL) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->expectedRegex = $expectedRegex;
        }
    }

    /**
     * @return string
     */
    public function expectedString()
    {
        return $this->expectedString;
    }

    /**
     * @param  string  $expectedString
     */
    public function expectOutputString($expectedString)
    {
        if ($this->expectedRegex !== NULL) {
            throw new PHPUnit_Framework_Exception;
        }

        if (is_string($expectedString) || is_null($expectedString)) {
            $this->expectedString = $expectedString;
        }
    }

    /**
     * @return mixed
     * @throws RuntimeException
     */
    protected function runTest()
    {
        ob_start();
        $this->obActive = TRUE;

        try {
            $testResult = parent::runTest();
        }

        catch (Exception $e) {
            ob_end_clean();
            $this->obActive = FALSE;
            throw $e;
        }

        if ($this->outputCallback === FALSE) {
            $this->output = ob_get_contents();
        } else {
            $this->output = call_user_func_array($this->outputCallback, array(ob_get_contents()));
        }

        ob_end_clean();
        $this->obActive = FALSE;

        if ($this->expectedRegex !== NULL) {
            $this->assertRegExp($this->expectedRegex, $this->output);
            $this->expectedRegex = NULL;
        }

        else if ($this->expectedString !== NULL) {
            $this->assertEquals($this->expectedString, $this->output);
            $this->expectedString = NULL;
        }

        return $testResult;
    }
}
