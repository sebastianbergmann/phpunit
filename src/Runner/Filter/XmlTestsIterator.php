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

use Exception;
use LibXMLError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use RecursiveFilterIterator;
use RecursiveIterator;
use SimpleXMLElement;

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
        $xml = $this->parseXmlFromFile($xmlFile);

        // Regular PHPUnit tests
        foreach ($xml->testCaseClass as $class) {
            $className = $class['name']->__toString();

            if (!isset($this->filter[$className])) {
                $this->filter[$className] = [];
            }

            foreach ($class->children() as $method) {
                $methodName = $method['name']->__toString();

                $dataSet = $method['dataSet'];

                if (null === $dataSet) {
                    $this->filter[$className][$methodName] = true;

                    continue;
                }

                $dataSet                         = $dataSet->__toString();
                $name                            = "{$methodName} with data set {$dataSet}";
                $this->filter[$className][$name] = true;
            }
        }

        // Handle PHP tests
        $this->filter[PhptTestCase::class] = [];

        foreach ($xml->phptFile as $phptFile) {
            $this->filter[PhptTestCase::class][$phptFile['path']->__toString()] = true;
        }
    }

    /**
     * @throws Exception
     */
    private function parseXmlFromFile(string $xmlFile): SimpleXMLElement
    {
        $oldValue = libxml_use_internal_errors(true);
        $xml      = simplexml_load_file($xmlFile);
        libxml_use_internal_errors($oldValue);

        if (false === $xml) {
            $xmlError = libxml_get_last_error();

            throw $this->xmlErrorToException($xmlError);
        }

        return $xml;
    }

    private function xmlErrorToException(LibXMLError $xmlError): Exception
    {
        return new Exception(
            sprintf(
                'Error parsing XML tests: %s in %s:%d:%d',
                trim($xmlError->message),
                $xmlError->file,
                $xmlError->line,
                $xmlError->column
            )
        );
    }
}
