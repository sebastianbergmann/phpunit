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
 * @since      File available since Release 2.3.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/TestDox/NamePrettifier.php';
require_once 'PHPUnit/Util/Printer.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Base class for printers of TestDox documentation.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 * @abstract
 */
abstract class PHPUnit_Util_TestDox_ResultPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    PHPUnit_Util_TestDox_NamePrettifier
     * @access protected
     */
    protected $prettifier;

    /**
     * @var    string
     * @access protected
     */
    protected $testClass = '';

    /**
     * @var    boolean
     * @access protected
     */
    protected $testFailed = FALSE;

    /**
     * @var    array
     * @access protected
     */
    protected $tests = array();

    /**
     * Constructor.
     *
     * @param  resource  $out
     * @access public
     */
    public function __construct($out = NULL)
    {
        parent::__construct($out);

        $this->prettifier = new PHPUnit_Util_TestDox_NamePrettifier;
        $this->startRun();
    }

    /**
     * Flush buffer and close output.
     *
     * @access public
     */
    public function flush()
    {
        $this->doEndClass();
        $this->endRun();

        parent::flush();
    }

    /**
     * Abstract Factory.
     *
     * @param  string    $type
     * @param  resource  $out
     * @throws RuntimeException
     * @access public
     * @static
     */
    public static function factory($type, $out = NULL)
    {
        $class = 'PHPUnit_Util_TestDox_ResultPrinter_' . $type;
        return new $class($out);
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->testFailed = TRUE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     * @access public
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->testFailed = TRUE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->testFailed = TRUE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->testFailed = TRUE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $class = get_class($test);

        if ($this->testClass != $class) {
            if ($this->testClass != '') {
                $this->doEndClass();
            }

            $this->startClass($this->prettifier->prettifyTestClass($class));

            $this->testClass = $class;
            $this->tests     = array();
        }

        $this->testFailed = FALSE;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $prettifiedName = $this->prettifier->prettifyTestMethod($test->getName());

        if (!isset($this->tests[$prettifiedName])) {
            if (!$this->testFailed) {
                $this->tests[$prettifiedName]['success'] = 1;
                $this->tests[$prettifiedName]['failure'] = 0;
            } else {
                $this->tests[$prettifiedName]['success'] = 0;
                $this->tests[$prettifiedName]['failure'] = 1;
            }
        } else {
            if (!$this->testFailed) {
                $this->tests[$prettifiedName]['success']++;
            } else {
                $this->tests[$prettifiedName]['failure']++;
            }
        }
    }

    /**
     * @access private
     * @since  Method available since Release 2.3.0
     */
    private function doEndClass()
    {
        foreach ($this->tests as $name => $data) {
            if ($data['failure'] == 0) {
                $this->onTest($name);
            }
        }

        $this->endClass($this->prettifier->prettifyTestClass($this->testClass));
    }

    /**
     * Handler for 'start run' event.
     *
     * @access protected
     */
    protected function startRun()
    {
    }

    /**
     * Handler for 'start class' event.
     *
     * @param  string $name
     * @access protected
     * @abstract
     */
    abstract protected function startClass($name);

    /**
     * Handler for 'on test' event.
     *
     * @param  string $name
     * @access protected
     * @abstract
     */
    abstract protected function onTest($name);

    /**
     * Handler for 'end class' event.
     *
     * @param  string $name
     * @access protected
     * @abstract
     */
    abstract protected function endClass($name);

    /**
     * Handler for 'end run' event.
     *
     * @access protected
     */
    protected function endRun()
    {
    }
}

require_once 'PHPUnit/Util/TestDox/ResultPrinter/HTML.php';
require_once 'PHPUnit/Util/TestDox/ResultPrinter/Text.php';
?>
