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
use function is_int;
use function is_numeric;
use function sprintf;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\DataFromTestDependency;
use PHPUnit\Event\TestData\MoreThanOneDataSetFromDataProviderException;
use PHPUnit\Event\TestData\NoDataSetFromDataProviderException;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Util\Reflection;
use SebastianBergmann\Exporter\Exporter;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TestMethod extends Test
{
    /**
     * @psalm-var class-string
     */
    private readonly string $className;

    /**
     * @psalm-var non-empty-string
     */
    private readonly string $methodName;
    private readonly int $line;
    private readonly TestDox $testDox;
    private readonly MetadataCollection $metadata;
    private readonly TestDataCollection $testData;

    /**
     * @throws MoreThanOneDataSetFromDataProviderException
     */
    public static function fromTestCase(TestCase $testCase): self
    {
        $methodName = $testCase->name();

        assert(!empty($methodName));

        $location = Reflection::sourceLocationFor($testCase::class, $methodName);

        return new self(
            $testCase::class,
            $methodName,
            $location['file'],
            $location['line'],
            TestDox::fromTestCase($testCase),
            MetadataCollection::for($testCase::class, $methodName),
            self::dataFor($testCase),
        );
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     */
    public function __construct(string $className, string $methodName, string $file, int $line, TestDox $testDox, MetadataCollection $metadata, TestDataCollection $testData)
    {
        parent::__construct($file);

        $this->className  = $className;
        $this->methodName = $methodName;
        $this->line       = $line;
        $this->testDox    = $testDox;
        $this->metadata   = $metadata;
        $this->testData   = $testData;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @psalm-return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    public function line(): int
    {
        return $this->line;
    }

    public function testDox(): TestDox
    {
        return $this->testDox;
    }

    public function metadata(): MetadataCollection
    {
        return $this->metadata;
    }

    public function testData(): TestDataCollection
    {
        return $this->testData;
    }

    /**
     * @psalm-assert-if-true TestMethod $this
     */
    public function isTestMethod(): bool
    {
        return true;
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    public function id(): string
    {
        $buffer = $this->className . '::' . $this->methodName;

        if ($this->testData()->hasDataFromDataProvider()) {
            $buffer .= '#' . $this->testData->dataFromDataProvider()->dataSetName();
        }

        return $buffer;
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    public function nameWithClass(): string
    {
        return $this->className . '::' . $this->name();
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    public function name(): string
    {
        if (!$this->testData->hasDataFromDataProvider()) {
            return $this->methodName;
        }

        $dataSetName = $this->testData->dataFromDataProvider()->dataSetName();

        if (is_int($dataSetName)) {
            $dataSetName = sprintf(
                ' with data set #%d',
                $dataSetName
            );
        } else {
            $dataSetName = sprintf(
                ' with data set "%s"',
                $dataSetName
            );
        }

        return $this->methodName . $dataSetName;
    }

    /**
     * @throws MoreThanOneDataSetFromDataProviderException
     */
    private static function dataFor(TestCase $testCase): TestDataCollection
    {
        $testData = [];

        if ($testCase->usesDataProvider()) {
            $dataSetName = $testCase->dataName();

            if (is_numeric($dataSetName)) {
                $dataSetName = (int) $dataSetName;
            }

            $testData[] = DataFromDataProvider::from(
                $dataSetName,
                (new Exporter)->export($testCase->providedData())
            );
        }

        if ($testCase->hasDependencyInput()) {
            $testData[] = DataFromTestDependency::from(
                (new Exporter)->export($testCase->dependencyInput())
            );
        }

        return TestDataCollection::fromArray($testData);
    }
}
