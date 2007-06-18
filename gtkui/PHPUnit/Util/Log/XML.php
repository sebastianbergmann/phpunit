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
require_once 'PHPUnit/Util/Printer.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestListener that generates an XML-based logfile
 * of the test execution.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
class PHPUnit_Util_Log_XML extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    DOMDocument
     * @access private
     */
    private $document;

    /**
     * @var    DOMElement
     * @access private
     */
    private $root;

    /**
     * @var    boolean
     * @access private
     */
    private $writeDocument = TRUE;

    /**
     * @var    DOMElement[]
     * @access private
     */
    private $testSuites = array();

    /**
     * @var    integer[]
     * @access private
     */
    private $testSuiteTests = array(0);

    /**
     * @var    integer[]
     * @access private
     */
    private $testSuiteErrors = array(0);

    /**
     * @var    integer[]
     * @access private
     */
    private $testSuiteFailures = array(0);

    /**
     * @var    integer[]
     * @access private
     */
    private $testSuiteTimes = array(0);

    /**
     * @var    integer
     * @access private
     */
    private $testSuiteLevel = 0;

    /**
     * @var    DOMElement
     * @access private
     */
    private $currentTestCase = NULL;

    /**
     * Constructor.
     *
     * @param  mixed $out
     * @access public
     */
    public function __construct($out = NULL)
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = TRUE;

        $this->root = $this->document->createElement('testsuites');
        $this->document->appendChild($this->root);

        parent::__construct($out);
    }

    /**
     * Flush buffer and close output.
     *
     * @access public
     */
    public function flush()
    {
        if ($this->writeDocument === TRUE) {
            $this->write($this->getXML());
        }

        parent::flush();
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
        $error = $this->document->createElement('error', PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE));
        $error->setAttribute('message', $e->getMessage());
        $error->setAttribute('type', get_class($e));

        $this->currentTestCase->appendChild($error);

        $this->testSuiteErrors[$this->testSuiteLevel]++;
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
        $failure = $this->document->createElement('failure', PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE));
        $failure->setAttribute('message', $e->getMessage());
        $failure->setAttribute('type', get_class($e));

        $this->currentTestCase->appendChild($failure);

        $this->testSuiteFailures[$this->testSuiteLevel]++;
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
        $error = $this->document->createElement('error', PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE));
        $error->setAttribute('message', 'Incomplete Test');
        $error->setAttribute('type', get_class($e));

        $this->currentTestCase->appendChild($error);

        $this->testSuiteErrors[$this->testSuiteLevel]++;
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
        $error = $this->document->createElement('error', PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE));
        $error->setAttribute('message', 'Skipped Test');
        $error->setAttribute('type', get_class($e));

        $this->currentTestCase->appendChild($error);

        $this->testSuiteErrors[$this->testSuiteLevel]++;
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
        $testSuite = $this->document->createElement('testsuite');
        $testSuite->setAttribute('name', $suite->getName());

        if (class_exists($suite->getName(), FALSE)) {
            try {
                $class      = new ReflectionClass($suite->getName());
                $docComment = $class->getDocComment();

                if (preg_match('/@category[\s]+([\.\w]+)/', $docComment, $matches)) {
                    $testSuite->setAttribute('category', $matches[1]);
                }

                if (preg_match('/@package[\s]+([\.\w]+)/', $docComment, $matches)) {
                    $testSuite->setAttribute('package', $matches[1]);
                }

                if (preg_match('/@subpackage[\s]+([\.\w]+)/', $docComment, $matches)) {
                    $testSuite->setAttribute('subpackage', $matches[1]);
                }
            }

            catch (ReflectionException $e) {
            }
        }

        if ($this->testSuiteLevel > 0) {
            $this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
        } else {
            $this->root->appendChild($testSuite);
        }

        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel]        = $testSuite;
        $this->testSuiteTests[$this->testSuiteLevel]    = 0;
        $this->testSuiteErrors[$this->testSuiteLevel]   = 0;
        $this->testSuiteFailures[$this->testSuiteLevel] = 0;
        $this->testSuiteTimes[$this->testSuiteLevel]    = 0;
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
        $this->testSuites[$this->testSuiteLevel]->setAttribute('tests', $this->testSuiteTests[$this->testSuiteLevel]);
        $this->testSuites[$this->testSuiteLevel]->setAttribute('failures', $this->testSuiteFailures[$this->testSuiteLevel]);
        $this->testSuites[$this->testSuiteLevel]->setAttribute('errors', $this->testSuiteErrors[$this->testSuiteLevel]);
        $this->testSuites[$this->testSuiteLevel]->setAttribute('time', $this->testSuiteTimes[$this->testSuiteLevel]);

        if ($this->testSuiteLevel > 1) {
            $this->testSuiteTests[$this->testSuiteLevel - 1]    += $this->testSuiteTests[$this->testSuiteLevel];
            $this->testSuiteErrors[$this->testSuiteLevel - 1]   += $this->testSuiteErrors[$this->testSuiteLevel];
            $this->testSuiteFailures[$this->testSuiteLevel - 1] += $this->testSuiteFailures[$this->testSuiteLevel];
            $this->testSuiteTimes[$this->testSuiteLevel - 1]    += $this->testSuiteTimes[$this->testSuiteLevel];
        }

        $this->testSuiteLevel--;
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $testCase = $this->document->createElement('testcase');
        $testCase->setAttribute('name', $test->getName());
        $testCase->setAttribute('class', get_class($test));

        $this->testSuites[$this->testSuiteLevel]->appendChild($testCase);
        $this->currentTestCase = $testCase;

        $this->testSuiteTests[$this->testSuiteLevel]++;
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
        $this->currentTestCase->setAttribute('time', $time);
        $this->testSuiteTimes[$this->testSuiteLevel] += $time;

        $this->currentTestCase = NULL;
    }

    /**
     * Returns the XML as a string.
     *
     * @return string
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function getXML()
    {
        return $this->document->saveXML();
    }

    /**
     * Enables or disables the writing of the document
     * in flush().
     *
     * This is a "hack" needed for the integration of
     * PHPUnit with Phing.
     *
     * @return string
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function setWriteDocument($flag)
    {
        if (is_bool($flag)) {
            $this->writeDocument = $flag;
        }
    }
}
?>
