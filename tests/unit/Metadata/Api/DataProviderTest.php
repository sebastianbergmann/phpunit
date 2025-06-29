<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\DuplicateKeyDataProvidersTest;
use PHPUnit\TestFixture\DuplicateKeyDataProviderTest;
use PHPUnit\TestFixture\InvalidKeyDataProviderTest;
use PHPUnit\TestFixture\MultipleDataProviderTest;
use PHPUnit\TestFixture\TestWithAttributeDataProviderTest;
use PHPUnit\TestFixture\VariousIterableDataProviderTest;

#[CoversClass(DataProvider::class)]
#[Small]
#[Group('metadata')]
final class DataProviderTest extends TestCase
{
    /**
     * Check if all data providers are being merged.
     */
    public function testMultipleDataProviders(): void
    {
        $this->checkMultipleProviders('testOne');
    }

    public function testMultipleYieldIteratorDataProviders(): void
    {
        $this->checkMultipleProviders('testTwo');
    }

    public function testWithVariousIterableDataProvidersFromParent(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testFromParent');

        $this->assertEquals([
            'asArrayProviderInParent'       => [['J'], ['K'], ['L']],
            'asIteratorProviderInParent'    => [['M'], ['N'], ['O']],
            'asTraversableProviderInParent' => [['P'], ['Q'], ['R']],
        ], $dataSets);
    }

    public function testWithVariousIterableDataProvidersInParent(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testInParent');

        $this->assertEquals([
            'asArrayProviderInParent'       => [['J'], ['K'], ['L']],
            'asIteratorProviderInParent'    => [['M'], ['N'], ['O']],
            'asTraversableProviderInParent' => [['P'], ['Q'], ['R']],
        ], $dataSets);
    }

    public function testWithVariousIterableAbstractDataProviders(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testAbstract');

        $this->assertEquals([
            'asArrayProvider'       => [['S'], ['T'], ['U']],
            'asIteratorProvider'    => [['V'], ['W'], ['X']],
            'asTraversableProvider' => [['Y'], ['Z'], ['P']],
        ], $dataSets);
    }

    public function testWithVariousIterableStaticDataProviders(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testStatic');

        $this->assertEquals([
            'asArrayStaticProvider'       => [['A'], ['B'], ['C']],
            'asIteratorStaticProvider'    => [['D'], ['E'], ['F']],
            'asTraversableStaticProvider' => [['G'], ['H'], ['I']],
        ], $dataSets);
    }

    public function testWithVariousIterableNonStaticDataProviders(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testNonStatic');

        $this->assertEquals([
            'asArrayProvider'       => [['S'], ['T'], ['U']],
            'asIteratorProvider'    => [['V'], ['W'], ['X']],
            'asTraversableProvider' => [['Y'], ['Z'], ['P']],
        ], $dataSets);
    }

    public function testWithInvalidKeyDataProvider(): void
    {
        $this->expectException(InvalidDataProviderException::class);
        $this->expectExceptionMessage('The key must be an integer or a string, bool given');

        /* @noinspection UnusedFunctionResultInspection */
        (new DataProvider)->providedData(InvalidKeyDataProviderTest::class, 'test');
    }

    public function testWithDuplicateKeyDataProvider(): void
    {
        $this->expectException(InvalidDataProviderException::class);
        $this->expectExceptionMessage('The key "foo" has already been defined by a previous data provider');

        /* @noinspection UnusedFunctionResultInspection */
        (new DataProvider)->providedData(DuplicateKeyDataProviderTest::class, 'test');
    }

    public function testTestWithAttribute(): void
    {
        $dataSets = (new DataProvider)->providedData(TestWithAttributeDataProviderTest::class, 'testWithAttribute');

        $this->assertSame(['testWith' => [
            'foo' => ['a', 'b'],
            'bar' => ['c', 'd'],
            0     => ['e', 'f'],
            1     => ['g', 'h'],
        ]], $dataSets);
    }

    public function testTestWithAttributeWithDuplicateKey(): void
    {
        $this->expectException(InvalidDataProviderException::class);
        $this->expectExceptionMessage('The key "foo" has already been defined by a previous TestWith attribute');

        /* @noinspection UnusedFunctionResultInspection */
        (new DataProvider)->providedData(TestWithAttributeDataProviderTest::class, 'testWithDuplicateName');
    }

    public function testWithDuplicateKeyDataProviders(): void
    {
        $this->expectException(InvalidDataProviderException::class);
        $this->expectExceptionMessage('The key "bar" has already been defined by a previous data provider');

        /* @noinspection UnusedFunctionResultInspection */
        (new DataProvider)->providedData(DuplicateKeyDataProvidersTest::class, 'test');
    }

    private function checkMultipleProviders(string $testMethodName): void
    {
        $dataSetsByProvider = (new DataProvider)->providedData(MultipleDataProviderTest::class, $testMethodName);
        $this->assertCount(3, $dataSetsByProvider);

        $counts = ['a' => 0, 'b' => 0, 'c' => 0];
        $pos    = ['a' => 0, 'b' => 1, 'c' => 2];

        foreach ($dataSetsByProvider as $dataSet) {
            for ($i = 0; $i < 3; $i++) {
                foreach ($pos as $which => $where) {
                    if ($dataSet[$i][$where] !== null) {
                        $counts[$which]++;
                    }
                }
            }
        }

        $this->assertEquals(3, $counts['a']);
        $this->assertEquals(3, $counts['b']);
        $this->assertEquals(3, $counts['c']);
    }
}
