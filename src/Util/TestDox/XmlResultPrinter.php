<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Printer;
use ReflectionClass;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class XmlResultPrinter extends Printer implements TestListener
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
     * @var NamePrettifier
     */
    private $prettifier;

    /**
     * @var null|\Throwable
     */
    private $exception;

    /**
     * @param resource|string $out
     *
     * @throws Exception
     */
    public function __construct($out = null)
    {
        $this->document               = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;

        $this->root = $this->document->createElement('tests');
        $this->document->appendChild($this->root);

        $this->prettifier = new NamePrettifier;

        parent::__construct($out);
    }

    /**
     * Flush buffer and close output.
     */
    public function flush(): void
    {
        $this->write($this->document->saveXML());

        parent::flush();
    }

    /**
     * An error occurred.
     */
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->exception = $t;
    }

    /**
     * A warning occurred.
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }

    /**
     * A failure occurred.
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->exception = $e;
    }

    /**
     * Incomplete test.
     */
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }

    /**
     * Risky test.
     */
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }

    /**
     * Skipped test.
     */
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }

    /**
     * A test suite started.
     */
    public function startTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A test suite ended.
     */
    public function endTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A test started.
     */
    public function startTest(Test $test): void
    {
        $this->exception = null;
    }

    /**
     * A test ended.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        /* @var TestCase $test */

        $groups = \array_filter(
            $test->getGroups(),
            function ($group) {
                return !($group === 'small' || $group === 'medium' || $group === 'large');
            }
        );

        $node = $this->document->createElement('test');

        $node->setAttribute('className', \get_class($test));
        $node->setAttribute('methodName', $test->getName());
        $node->setAttribute('prettifiedClassName', $this->prettifier->prettifyTestClass(\get_class($test)));
        $node->setAttribute('prettifiedMethodName', $this->prettifier->prettifyTestCase($test));
        $node->setAttribute('status', (string) $test->getStatus());
        $node->setAttribute('time', (string) $time);
        $node->setAttribute('size', (string) $test->getSize());
        $node->setAttribute('groups', \implode(',', $groups));

        $inlineAnnotations = \PHPUnit\Util\Test::getInlineAnnotations(\get_class($test), $test->getName());

        if (isset($inlineAnnotations['given'], $inlineAnnotations['when'], $inlineAnnotations['then'])) {
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
                if (isset($step['file']) && $step['file'] === $file) {
                    $node->setAttribute('exceptionLine', (string) $step['line']);

                    break;
                }
            }

            $node->setAttribute('exceptionMessage', $this->exception->getMessage());
        }

        $this->root->appendChild($node);
    }
}
