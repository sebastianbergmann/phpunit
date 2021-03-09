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

use function array_filter;
use function assert;
use function get_class;
use function strpos;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\WarningTestCase;
use PHPUnit\Util\Metadata\Covers;
use PHPUnit\Util\Metadata\CoversClass;
use PHPUnit\Util\Metadata\CoversFunction;
use PHPUnit\Util\Metadata\CoversMethod;
use PHPUnit\Util\Metadata\InlineAnnotationParser;
use PHPUnit\Util\Metadata\Registry as MetadataRegistry;
use PHPUnit\Util\Metadata\Uses;
use PHPUnit\Util\Metadata\UsesClass;
use PHPUnit\Util\Metadata\UsesFunction;
use PHPUnit\Util\Metadata\UsesMethod;
use PHPUnit\Util\Printer;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class XmlResultPrinter extends Printer implements TestListener
{
    private DOMDocument $document;

    private DOMElement $root;

    private NamePrettifier $prettifier;

    private ?Throwable $exception = null;

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
    public function addError(Test $test, Throwable $t, float $time): void
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
    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
    }

    /**
     * Risky test.
     */
    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
    }

    /**
     * Skipped test.
     */
    public function addSkippedTest(Test $test, Throwable $t, float $time): void
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
     */
    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase || $test instanceof WarningTestCase) {
            return;
        }

        $groups = array_filter(
            $test->groups(),
            static function ($group) {
                return !($group === 'small' || $group === 'medium' || $group === 'large' || strpos($group, '__phpunit_') === 0);
            }
        );

        $testNode = $this->document->createElement('test');

        $testNode->setAttribute('className', get_class($test));
        $testNode->setAttribute('methodName', $test->getName());
        $testNode->setAttribute('prettifiedClassName', $this->prettifier->prettifyTestClass(get_class($test)));
        $testNode->setAttribute('prettifiedMethodName', $this->prettifier->prettifyTestCase($test));
        $testNode->setAttribute('status', $test->status()->asString());
        $testNode->setAttribute('time', (string) $time);
        $testNode->setAttribute('size', $test->size()->asString());

        foreach ($groups as $group) {
            $groupNode = $this->document->createElement('group');

            $groupNode->setAttribute('name', $group);

            $testNode->appendChild($groupNode);
        }

        foreach (MetadataRegistry::parser()->forClassAndMethod(get_class($test), $test->getName(false)) as $metadata) {
            if ($metadata->isCovers()) {
                assert($metadata instanceof Covers);

                $coversNode = $this->document->createElement('covers');

                $coversNode->setAttribute('target', $metadata->target());

                $testNode->appendChild($coversNode);
            }

            if ($metadata->isCoversClass()) {
                assert($metadata instanceof CoversClass);

                $coversNode = $this->document->createElement('covers');

                $coversNode->setAttribute('target', $metadata->className());

                $testNode->appendChild($coversNode);
            }

            if ($metadata->isCoversMethod()) {
                assert($metadata instanceof CoversMethod);

                $coversNode = $this->document->createElement('covers');

                $coversNode->setAttribute('target', $metadata->className() . '::' . $metadata->methodName());

                $testNode->appendChild($coversNode);
            }

            if ($metadata->isCoversFunction()) {
                assert($metadata instanceof CoversFunction);

                $coversNode = $this->document->createElement('covers');

                $coversNode->setAttribute('target', $metadata->functionName());

                $testNode->appendChild($coversNode);
            }

            if ($metadata->isUses()) {
                assert($metadata instanceof Uses);

                $coversNode = $this->document->createElement('uses');

                $coversNode->setAttribute('target', $metadata->target());

                $testNode->appendChild($coversNode);
            }

            if ($metadata->isUsesClass()) {
                assert($metadata instanceof UsesClass);

                $coversNode = $this->document->createElement('uses');

                $coversNode->setAttribute('target', $metadata->className());

                $testNode->appendChild($coversNode);
            }

            if ($metadata->isUsesMethod()) {
                assert($metadata instanceof UsesMethod);

                $coversNode = $this->document->createElement('uses');

                $coversNode->setAttribute('target', $metadata->className() . '::' . $metadata->methodName());

                $testNode->appendChild($coversNode);
            }

            if ($metadata->isUsesFunction()) {
                assert($metadata instanceof UsesFunction);

                $coversNode = $this->document->createElement('uses');

                $coversNode->setAttribute('target', $metadata->functionName());

                $testNode->appendChild($coversNode);
            }
        }

        foreach ($test->doubledTypes() as $doubledType) {
            $testDoubleNode = $this->document->createElement('testDouble');

            $testDoubleNode->setAttribute('type', $doubledType);

            $testNode->appendChild($testDoubleNode);
        }

        $inlineAnnotations = (new InlineAnnotationParser)->parse(
            get_class($test),
            $test->getName(false)
        );

        if (isset($inlineAnnotations['given'], $inlineAnnotations['when'], $inlineAnnotations['then'])) {
            $testNode->setAttribute('given', $inlineAnnotations['given']['value']);
            $testNode->setAttribute('givenStartLine', (string) $inlineAnnotations['given']['line']);
            $testNode->setAttribute('when', $inlineAnnotations['when']['value']);
            $testNode->setAttribute('whenStartLine', (string) $inlineAnnotations['when']['line']);
            $testNode->setAttribute('then', $inlineAnnotations['then']['value']);
            $testNode->setAttribute('thenStartLine', (string) $inlineAnnotations['then']['line']);
        }

        if ($this->exception !== null) {
            if ($this->exception instanceof Exception) {
                $steps = $this->exception->getSerializableTrace();
            } else {
                $steps = $this->exception->getTrace();
            }

            try {
                $file = (new ReflectionClass($test))->getFileName();
                // @codeCoverageIgnoreStart
            } catch (ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

            foreach ($steps as $step) {
                if (isset($step['file']) && $step['file'] === $file) {
                    $testNode->setAttribute('exceptionLine', (string) $step['line']);

                    break;
                }
            }

            $testNode->setAttribute('exceptionMessage', $this->exception->getMessage());
        }

        $this->root->appendChild($testNode);
    }
}
