<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use function assert;
use function debug_backtrace;
use function is_numeric;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\DataFromTestDependency;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Util\Reflection;
use SebastianBergmann\Exporter\Exporter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestMethodBuilder
{
    public static function fromTestCase(TestCase $testCase): TestMethod
    {
        $methodName = $testCase->name();

        assert(!empty($methodName));

        $location = Reflection::sourceLocationFor($testCase::class, $methodName);

        return new TestMethod(
            $testCase::class,
            $methodName,
            $location['file'],
            $location['line'],
            TestDoxBuilder::fromTestCase($testCase),
            MetadataRegistry::parser()->forClassAndMethod($testCase::class, $methodName),
            self::dataFor($testCase),
        );
    }

    /**
     * @throws NoTestCaseObjectOnCallStackException
     */
    public static function fromCallStack(): TestMethod
    {
        foreach (debug_backtrace() as $frame) {
            if (isset($frame['object']) && $frame['object'] instanceof TestCase) {
                return $frame['object']->valueObjectForEvents();
            }
        }

        throw new NoTestCaseObjectOnCallStackException;
    }

    private static function dataFor(TestCase $testCase): TestDataCollection
    {
        $exporter = new Exporter;
        $testData = [];

        if ($testCase->usesDataProvider()) {
            $dataSetName = $testCase->dataName();

            if (is_numeric($dataSetName)) {
                $dataSetName = (int) $dataSetName;
            }

            $testData[] = DataFromDataProvider::from(
                $dataSetName,
                $exporter->export($testCase->providedData()),
            );
        }

        if ($testCase->hasDependencyInput()) {
            $testData[] = DataFromTestDependency::from(
                $exporter->export($testCase->dependencyInput()),
            );
        }

        return TestDataCollection::fromArray($testData);
    }
}
