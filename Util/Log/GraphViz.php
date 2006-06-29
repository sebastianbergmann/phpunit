<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 3.0.0
 */

@include_once 'Image/GraphViz.php';

require_once 'PHPUnit2/Framework.php';
require_once 'PHPUnit2/Util/Filter.php';
require_once 'PHPUnit2/Util/Filesystem.php';
require_once 'PHPUnit2/Util/Test.php';
require_once 'PHPUnit2/Util/Timer.php';

PHPUnit2_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestListener that generates maps of the executed tests
 * in GraphViz markup.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 3.0.0
 */
class PHPUnit2_Util_Log_GraphViz implements PHPUnit2_Framework_TestListener
{
    /**
     * @var    Image_GraphViz[]
     * @access private
     */
    private $graphs = array();

    /**
     * @var    string
     * @access private
     */
    private $target;

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
     * @param  string $target
     * @access public
     */
    public function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e)
    {
        $this->addTestNode($test, 'red');
        $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit2_Framework_Test                 $test
     * @param  PHPUnit2_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)
    {
        $this->addTestNode($test, 'red');
        $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)
    {
        $this->addTestNode($test, 'yellow');
        $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addSkippedTest(PHPUnit2_Framework_Test $test, Exception $e)
    {
        $this->addTestNode($test, 'yellow');
        $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel]++;

        $this->currentTestSuccess = FALSE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite)
    {
        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel]                        = $suite->getName();
        $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel]      = 0;
        $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel] = 0;

        $this->graphs[$this->testSuiteLevel] = new Image_GraphViz(
          TRUE,
          array(
            'overlap'  => 'scale',
            'splines'  => 'true',
            'sep'      => '.1',
            'fontsize' => '8'
          )
        );

        $this->graphs[$this->testSuiteLevel]->addNode($suite->getName());

        if ($this->testSuiteLevel > 1) {
            $this->graphs[$this->testSuiteLevel]->addNode(
              $this->testSuites[$this->testSuiteLevel - 1],
              array(
                'URL' => PHPUnit2_Util_Filesystem::getSafeFilename($this->testSuites[$this->testSuiteLevel - 1]) . '-test.htm'
              )
            );

            $this->graphs[$this->testSuiteLevel]->addEdge(
              array(
                $this->testSuites[$this->testSuiteLevel - 1] => $suite->getName()
              )
            );

            $this->graphs[$this->testSuiteLevel - 1]->addNode($suite->getName());

            $this->graphs[$this->testSuiteLevel - 1]->addEdge(
              array(
                $this->testSuites[$this->testSuiteLevel - 1] => $suite->getName()
              )
            );
        }
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite)
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
        
        $safeSuiteName = PHPUnit2_Util_Filesystem::getSafeFilename($suite->getName());

        $this->graphs[$this->testSuiteLevel]->addNode(
          $this->testSuites[$this->testSuiteLevel],
          array(
            'color' => $color,
            'URL'   => $safeSuiteName . '-test.htm'
          )
        );

        if ($this->testSuiteLevel > 1) {
            $this->graphs[$this->testSuiteLevel - 1]->addNode(
              $this->testSuites[$this->testSuiteLevel],
              array(
                'color' => $color,
                'URL'   => $safeSuiteName . '-test.htm'
              )
            );

            $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel - 1]      += $this->testSuiteFailureOrErrorCount[$this->testSuiteLevel];
            $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel - 1] += $this->testSuiteIncompleteOrSkippedCount[$this->testSuiteLevel];
        }

        $fp = fopen($this->target . $safeSuiteName . '.dot', 'wt');
        fputs($fp, $this->graphs[$this->testSuiteLevel]->parse());
        fclose($fp);

        $this->testSuiteLevel--;
    }

    /**
     * A test started.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit2_Framework_Test $test)
    {
        $this->currentTestSuccess = TRUE;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function endTest(PHPUnit2_Framework_Test $test)
    {
        if ($this->currentTestSuccess) {
            $this->addTestNode($test, 'green');
        }
    }

    /**
     * @param  PHPUnit2_Framework_Test $test
     * @param  string                  $color
     * @access private
     */
    private function addTestNode(PHPUnit2_Framework_Test $test, $color)
    {
        $name = PHPUnit2_Util_Test::describe($test, FALSE);

        $this->graphs[$this->testSuiteLevel]->addNode(
          $name[1],
          array(
            'color' => $color,
            'URL'   => sprintf(
              '%s-test.htm#%s',

              PHPUnit2_Util_Filesystem::getSafeFilename($name[0]),
              $name[1]
            )
          ),
          $this->testSuites[$this->testSuiteLevel]
        );

        $this->graphs[$this->testSuiteLevel]->addEdge(
          array(
            $this->testSuites[$this->testSuiteLevel] => $name[1]
          )
        );
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
