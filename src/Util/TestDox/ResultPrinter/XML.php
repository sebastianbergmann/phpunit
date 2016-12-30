<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Test;

/**
 * @since Class available since Release 5.4.0
 */
class PHPUnit_Util_TestDox_ResultPrinter_XML extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var DOMElement
     */
    private $root;

    /**
     * @var PHPUnit_Util_TestDox_NamePrettifier
     */
    private $prettifier;

    /**
     * @var Exception
     */
    private $exception;

    /**
     * @param string|resource $out
     */
    public function __construct($out = null)
    {
        $this->document               = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $this->root = $this->document->createElement('tests');
        $this->document->appendChild($this->root);

        $this->prettifier = new PHPUnit_Util_TestDox_NamePrettifier;

        parent::__construct($out);
    }

    /**
     * Flush buffer and close output.
     */
    public function flush()
    {
        $this->write($this->document->saveXML());

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
        $this->exception = $e;
    }

    /**
     * A warning occurred.
     *
     * @param Test                      $test
     * @param PHPUnit_Framework_Warning $e
     * @param float                     $time
     */
    public function addWarning(Test $test, PHPUnit_Framework_Warning $e, $time)
    {
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
        $this->exception = $e;
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
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test)
    {
        $this->exception = null;
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        /* @var TestCase $test */

        $groups = array_filter(
            $test->getGroups(),
            function ($group) {
                if ($group == 'small' || $group == 'medium' || $group == 'large') {
                    return false;
                }

                return true;
            }
        );

        $node = $this->document->createElement('test');

        $node->setAttribute('className', get_class($test));
        $node->setAttribute('methodName', $test->getName());
        $node->setAttribute('prettifiedClassName', $this->prettifier->prettifyTestClass(get_class($test)));
        $node->setAttribute('prettifiedMethodName', $this->prettifier->prettifyTestMethod($test->getName()));
        $node->setAttribute('status', $test->getStatus());
        $node->setAttribute('time', $time);
        $node->setAttribute('size', $test->getSize());
        $node->setAttribute('groups', implode(',', $groups));

        $inlineAnnotations = PHPUnit_Util_Test::getInlineAnnotations(get_class($test), $test->getName());

        if (isset($inlineAnnotations['given']) && isset($inlineAnnotations['when']) && isset($inlineAnnotations['then'])) {
            $node->setAttribute('given', $inlineAnnotations['given']['value']);
            $node->setAttribute('givenStartLine', $inlineAnnotations['given']['line']);
            $node->setAttribute('when', $inlineAnnotations['when']['value']);
            $node->setAttribute('whenStartLine', $inlineAnnotations['when']['line']);
            $node->setAttribute('then', $inlineAnnotations['then']['value']);
            $node->setAttribute('thenStartLine', $inlineAnnotations['then']['line']);
        }

        if ($this->exception !== null) {
            if ($this->exception instanceof Exception) {
                $steps = $this->exception->getSerializableTrace();
            } else {
                $steps = $this->exception->getTrace();
            }

            $class = new ReflectionClass($test);
            $file  = $class->getFileName();

            foreach ($steps as $step) {
                if (isset($step['file']) && $step['file'] == $file) {
                    $node->setAttribute('exceptionLine', $step['line']);

                    break;
                }
            }

            $node->setAttribute('exceptionMessage', $this->exception->getMessage());
        }

        $this->root->appendChild($node);
    }
}
