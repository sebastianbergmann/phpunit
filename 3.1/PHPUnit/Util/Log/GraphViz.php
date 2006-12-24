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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
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

@include_once 'Image/GraphViz.php';

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Printer.php';
require_once 'PHPUnit/Util/Test.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestListener that generates maps of the executed tests
 * in GraphViz markup.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Log_GraphViz extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    Image_GraphViz
     * @access private
     */
    private $graph;

    /**
     * @var    boolean
     * @access private
     */
    private $currentTestSuccess = TRUE;

    /**
     * @var    string[]
     * @access private
     */
    private $testSuites = array();

    /**
     * @var    integer
     * @access private
     */
    private $testSuiteLevel = 0;

    /**
     * @var    integer[]
     * @access private
     */
    private $testSuiteFailureOrErrorCount = array(0);

    /**
     * @var    integer[]
     * @access private
     */
    private $testSuiteIncompleteOrSkippedCount = array(0);

    /**
     * Constructor.
     *
     * @param  mixed $out
     * @access public
     */
    public function __construct($out = NULL)
    {
        $this->graph = new Image_GraphViz(
          TRUE,
          array(
            'overlap'  => 'scale',
            'splines'  => 'true',
            'sep'      => '.1',
            'fontsize' => '8'
          )
        );

        parent::__construct($out);
    }

    /**
     * Flush buffer and close output.
     *
     * @access public
     */
    public function flush()
    {
        $this->write($this->graph->parse());

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
        $this->addTestNode($test, 'red');
        $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
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
        $this->addTestNode($test, 'red');
        $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
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
        $this->addTestNode($test, 'yellow');
        $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addTestNode($test, 'yellow');
        $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->graph->addNode($suite->getName());

        if ($this->testSuiteLevel > 0) {
            $this->graph->addEdge(
              array(
                $this->testSuites[$this->testSuiteLevel] => $suite->getName()
              )
            );
        }

        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel]                        = $suite->getName();
        $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel]      = 0;
        $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel] = 0;
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $color = 'red';

        if ($this->testSuiteFailureOrErrorCount[$this->testSuiteLevel] == 0 &&
            $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel] == 0) {
            $color = 'green';
        }

        else if ($this->testSuiteFailureOrErrorCount[$this->testSuiteLevel] == 0 &&
                 $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel] > 0) {
            $color = 'yellow';
        }

        $this->graph->addNode(
          $this->testSuites[$this->testSuiteLevel],
          array('color' => $color)
        );

        if ($this->testSuiteLevel > 1) {
            $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel - 1]      += $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel];
            $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel - 1] += $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel];
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
        $this->currentTestSuccess = TRUE;
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
        if ($this->currentTestSuccess) {
            $this->addTestNode($test, 'green');
        }
    }

    /**
     * @param  PHPUnit_Framework_Test $test
     * @param  string                  $color
     * @access private
     */
    private function addTestNode(PHPUnit_Framework_Test $test, $color)
    {
        $name = PHPUnit_Util_Test::describe($test, FALSE);

        $this->graph->addNode(
          $name[1],
          array('color' => $color),
          $this->testSuites[$this->testSuiteLevel]
        );

        $this->graph->addEdge(
          array(
            $this->testSuites[$this->testSuiteLevel] => $name[1]
          )
        );
    }
}
?>
