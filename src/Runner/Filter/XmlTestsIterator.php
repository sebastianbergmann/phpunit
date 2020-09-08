<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use DOMElement;
use DOMXPath;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class XmlTestsIterator extends RecursiveFilterIterator
{
    /**
     * The filter is used as a fast look for
     * - the class name
     * - the method name plus its optional data set description.
     *
     * Example: `filter[class name][method name + data set] = true;`
     *
     * The accept() method then can use a fast isset() to check if a test should
     * be included or not.
     *
     * This works equally for phpt tests, except we hardcode the class name.
     *
     * @var array<string,array<string,true>>
     */
    private array $filter = [];

    /**
     * @throws Exception
     */
    public function __construct(RecursiveIterator $iterator, string $xmlFile)
    {
        parent::__construct($iterator);

        $this->setFilter($xmlFile);
    }

    public function accept(): bool
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof TestSuite) {
            return true;
        }

        /** @var TestCase $test */
        $testClass = get_class($test);

        if (!isset($this->filter[$testClass])) {
            return false;
        }

        $name = $test->getName();

        return isset($this->filter[$testClass][$name]);
    }

    /**
     * @throws Exception
     */
    private function setFilter(string $xmlFile): void
    {
        $xml   = (new XmlLoader())->loadFile($xmlFile);
        $xpath = new DOMXPath($xml);

        foreach ($this->extractTestCases($xpath) as [$className, $methodName, $dataSet]) {
            if (!isset($this->filter[$className])) {
                $this->filter[$className] = [];
            }

            if (!$dataSet) {
                $this->filter[$className][$methodName] = true;

                continue;
            }

            $name                            = "{$methodName} with data set {$dataSet}";
            $this->filter[$className][$name] = true;
        }

        foreach ($this->extractPhptFile($xpath) as $path) {
            $this->filter[PhptTestCase::class][$path] = true;
        }
    }

    private function extractTestCases(DOMXPath $xpath): Generator
    {
        /** @var DOMElement $class */
        foreach ($xpath->evaluate('/tests/testCaseClass') as $class) {
            $className = $class->getAttribute('name');

            if (!$className) {
                continue;
            }

            /** @var DOMElement $method */
            foreach ($xpath->evaluate('testCaseMethod', $class) as $method) {
                $methodName = $method->getAttribute('name');

                if (!$methodName) {
                    continue;
                }

                $dataSet = $method->getAttribute('dataSet');

                yield [$className, $methodName, $dataSet];
            }
        }
    }

    /**
     * @return Generator<string>
     */
    private function extractPhptFile(DOMXPath $xpath): Generator
    {
        /* @var DOMElement $phptFile */
        foreach ($xpath->evaluate('/tests/phptFile') as $phptFile) {
            $path = $phptFile->getAttribute('path');

            if ($path) {
                yield $path;
            }
        }
    }
}
