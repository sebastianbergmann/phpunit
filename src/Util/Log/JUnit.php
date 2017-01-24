<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Log;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Printer;
use PHPUnit\Util\Xml;
use ReflectionClass;
use ReflectionException;

/**
 * A TestListener that generates a logfile of the test execution in XML markup.
 *
 * The XML markup used is the same as the one that is used by the JUnit Ant task.
 */
class JUnit extends Printer implements TestListener
{
    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var DOMElement
     */
    protected $root;

    /**
     * @var bool
     */
    protected $reportUselessTests = false;

    /**
     * @var bool
     */
    protected $writeDocument = true;

    /**
     * @var DOMElement[]
     */
    protected $testSuites = [];

    /**
     * @var int[]
     */
    protected $testSuiteTests = [0];

    /**
     * @var int[]
     */
    protected $testSuiteAssertions = [0];

    /**
     * @var int[]
     */
    protected $testSuiteErrors = [0];

    /**
     * @var int[]
     */
    protected $testSuiteFailures = [0];

    /**
     * @var int[]
     */
    protected $testSuiteSkipped = [0];

    /**
     * @var int[]
     */
    protected $testSuiteTimes = [0];

    /**
     * @var int
     */
    protected $testSuiteLevel = 0;

    /**
     * @var DOMElement
     */
    protected $currentTestCase = null;

    /**
     * Constructor.
     *
     * @param mixed $out
     * @param bool  $reportUselessTests
     */
    public function __construct($out = null, $reportUselessTests = false)
    {
        $this->document               = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $this->root = $this->document->createElement('testsuites');
        $this->document->appendChild($this->root);

        parent::__construct($out);

        $this->reportUselessTests = $reportUselessTests;
    }

    /**
     * Flush buffer and close output.
     */
    public function flush()
    {
        if ($this->writeDocument === true) {
            $this->write($this->getXML());
        }

        parent::flush();
    }

    /**
     * An error occurred.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addError(Test $test, \Exception $e, $time)
    {
        $this->doAddFault($test, $e, $time, 'error');
        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }

    /**
     * A warning occurred.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
        $this->doAddFault($test, $e, $time, 'warning');
        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        $this->doAddFault($test, $e, $time, 'failure');
        $this->testSuiteFailures[$this->testSuiteLevel]++;
    }

    /**
     * Incomplete test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addIncompleteTest(Test $test, \Exception $e, $time)
    {
        $this->doAddSkipped($test);
    }

    /**
     * Risky test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addRiskyTest(Test $test, \Exception $e, $time)
    {
        if (!$this->reportUselessTests || $this->currentTestCase === null) {
            return;
        }

        $error = $this->document->createElement(
            'error',
            Xml::prepareString(
                "Risky Test\n" .
                Filter::getFilteredStacktrace($e)
            )
        );

        $error->setAttribute('type', get_class($e));

        $this->currentTestCase->appendChild($error);

        $this->testSuiteErrors[$this->testSuiteLevel]++;
    }

    /**
     * Skipped test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addSkippedTest(Test $test, \Exception $e, $time)
    {
        $this->doAddSkipped($test);
    }

    /**
     * A testsuite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite)
    {
        $testSuite = $this->document->createElement('testsuite');
        $testSuite->setAttribute('name', $suite->getName());

        if (class_exists($suite->getName(), false)) {
            try {
                $class = new ReflectionClass($suite->getName());

                $testSuite->setAttribute('file', $class->getFileName());
            } catch (ReflectionException $e) {
            }
        }

        if ($this->testSuiteLevel > 0) {
            $this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
        } else {
            $this->root->appendChild($testSuite);
        }

        $this->testSuiteLevel++;
        $this->testSuites[$this->testSuiteLevel]          = $testSuite;
        $this->testSuiteTests[$this->testSuiteLevel]      = 0;
        $this->testSuiteAssertions[$this->testSuiteLevel] = 0;
        $this->testSuiteErrors[$this->testSuiteLevel]     = 0;
        $this->testSuiteFailures[$this->testSuiteLevel]   = 0;
        $this->testSuiteSkipped[$this->testSuiteLevel]    = 0;
        $this->testSuiteTimes[$this->testSuiteLevel]      = 0;
    }

    /**
     * A testsuite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'tests',
            $this->testSuiteTests[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'assertions',
            $this->testSuiteAssertions[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'errors',
            $this->testSuiteErrors[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'failures',
            $this->testSuiteFailures[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'skipped',
            $this->testSuiteSkipped[$this->testSuiteLevel]
        );

        $this->testSuites[$this->testSuiteLevel]->setAttribute(
            'time',
            sprintf('%F', $this->testSuiteTimes[$this->testSuiteLevel])
        );

        if ($this->testSuiteLevel > 1) {
            $this->testSuiteTests[$this->testSuiteLevel - 1]      += $this->testSuiteTests[$this->testSuiteLevel];
            $this->testSuiteAssertions[$this->testSuiteLevel - 1] += $this->testSuiteAssertions[$this->testSuiteLevel];
            $this->testSuiteErrors[$this->testSuiteLevel - 1]     += $this->testSuiteErrors[$this->testSuiteLevel];
            $this->testSuiteFailures[$this->testSuiteLevel - 1]   += $this->testSuiteFailures[$this->testSuiteLevel];
            $this->testSuiteSkipped[$this->testSuiteLevel - 1]    += $this->testSuiteSkipped[$this->testSuiteLevel];
            $this->testSuiteTimes[$this->testSuiteLevel - 1]      += $this->testSuiteTimes[$this->testSuiteLevel];
        }

        $this->testSuiteLevel--;
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test)
    {
        $testCase = $this->document->createElement('testcase');
        $testCase->setAttribute('name', $test->getName());

        if ($test instanceof TestCase) {
            $class      = new ReflectionClass($test);
            $methodName = $test->getName();

            if ($class->hasMethod($methodName)) {
                $method = $class->getMethod($test->getName());

                $testCase->setAttribute('class', $class->getName());
                $testCase->setAttribute('file', $class->getFileName());
                $testCase->setAttribute('line', $method->getStartLine());
            }
        }

        $this->currentTestCase = $testCase;
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        if ($test instanceof TestCase) {
            $numAssertions = $test->getNumAssertions();
            $this->testSuiteAssertions[$this->testSuiteLevel] += $numAssertions;

            $this->currentTestCase->setAttribute(
                'assertions',
                $numAssertions
            );
        }

        $this->currentTestCase->setAttribute(
            'time',
            sprintf('%F', $time)
        );

        $this->testSuites[$this->testSuiteLevel]->appendChild(
            $this->currentTestCase
        );

        $this->testSuiteTests[$this->testSuiteLevel]++;
        $this->testSuiteTimes[$this->testSuiteLevel] += $time;

        if (method_exists($test, 'hasOutput') && $test->hasOutput()) {
            $systemOut = $this->document->createElement('system-out');

            $systemOut->appendChild(
                $this->document->createTextNode($test->getActualOutput())
            );

            $this->currentTestCase->appendChild($systemOut);
        }

        $this->currentTestCase = null;
    }

    /**
     * Returns the XML as a string.
     *
     * @return string
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
     */
    public function setWriteDocument($flag)
    {
        if (is_bool($flag)) {
            $this->writeDocument = $flag;
        }
    }

    /**
     * Method which generalizes addError() and addFailure()
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     * @param string     $type
     */
    private function doAddFault(Test $test, \Exception $e, $time, $type)
    {
        if ($this->currentTestCase === null) {
            return;
        }

        if ($test instanceof SelfDescribing) {
            $buffer = $test->toString() . "\n";
        } else {
            $buffer = '';
        }

        $buffer .= TestFailure::exceptionToString($e) . PHP_EOL .
                   Filter::getFilteredStacktrace($e);

        $fault = $this->document->createElement(
            $type,
            Xml::prepareString($buffer)
        );

        if ($e instanceof ExceptionWrapper) {
            $fault->setAttribute('type', $e->getClassName());
        } else {
            $fault->setAttribute('type', get_class($e));
        }

        $this->currentTestCase->appendChild($fault);
    }

    private function doAddSkipped(Test $test)
    {
        if ($this->currentTestCase === null) {
            return;
        }

        $skipped = $this->document->createElement('skipped');
        $this->currentTestCase->appendChild($skipped);

        $this->testSuiteSkipped[$this->testSuiteLevel]++;
    }
}
