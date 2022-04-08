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

use PHPUnit\Event\DataFromDataProvider;
use PHPUnit\Event\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Util\ExportedVariable;

#[CoversClass(TestMethod::class)]
final class TestMethodTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $className  = 'ExampleTest';
        $methodName = 'testExample';
        $file       = 'ExampleTest.php';
        $line       = 1;
        $testData   = TestDataCollection::fromArray([]);
        $metadata   = MetadataCollection::fromArray([]);

        $test = new TestMethod(
            $className,
            $methodName,
            $file,
            $line,
            $metadata,
            $testData
        );

        $this->assertSame($className, $test->className());
        $this->assertSame($methodName, $test->methodName());
        $this->assertSame($file, $test->file());
        $this->assertSame($line, $test->line());
        $this->assertSame($metadata, $test->metadata());
        $this->assertSame($testData, $test->testData());
    }

    public function testNameReturnsNameWhenTestDoesNotHaveDataFromDataProvider(): void
    {
        $test = new TestMethod(
            'ExampleTest',
            'testExample',
            'ExampleTest.php',
            1,
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([])
        );

        $this->assertSame($test->methodName(), $test->name());
    }

    public function testNameReturnsNameWhenTestHasDataFromDataProviderAndDataSetNameIsInt(): void
    {
        $dataSetName = 9000;

        $test = new TestMethod(
            'ExampleTest',
            'testExample',
            'ExampleTest.php',
            1,
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([
                DataFromDataProvider::from(
                    $dataSetName,
                    ExportedVariable::from(
                        'foo',
                        false
                    )
                ),
            ])
        );

        $expected = sprintf(
            '%s with data set #%d',
            $test->methodName(),
            $dataSetName
        );

        $this->assertSame($expected, $test->name());
    }

    public function testNameReturnsNameWhenTestHasDataFromDataProviderAndDataSetNameIsString(): void
    {
        $dataSetName = 'bar-9000';

        $test = new TestMethod(
            'ExampleTest',
            'testExample',
            'ExampleTest.php',
            1,
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([
                DataFromDataProvider::from(
                    $dataSetName,
                    ExportedVariable::from(
                        'foo',
                        false
                    )
                ),
            ])
        );

        $expected = sprintf(
            '%s with data set "%s"',
            $test->methodName(),
            $dataSetName
        );

        $this->assertSame($expected, $test->name());
    }
}
