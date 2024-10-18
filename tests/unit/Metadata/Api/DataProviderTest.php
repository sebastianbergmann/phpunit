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
        $dataSets = (new DataProvider)->providedData(MultipleDataProviderTest::class, 'testOne');

        $this->assertCount(9, $dataSets);

        $aCount = 0;
        $bCount = 0;
        $cCount = 0;

        for ($i = 0; $i < 9; $i++) {
            $aCount += $dataSets[$i][0] != null ? 1 : 0;
            $bCount += $dataSets[$i][1] != null ? 1 : 0;
            $cCount += $dataSets[$i][2] != null ? 1 : 0;
        }

        $this->assertEquals(3, $aCount);
        $this->assertEquals(3, $bCount);
        $this->assertEquals(3, $cCount);
    }

    public function testMultipleYieldIteratorDataProviders(): void
    {
        $dataSets = (new DataProvider)->providedData(MultipleDataProviderTest::class, 'testTwo');

        $this->assertCount(9, $dataSets);

        $aCount = 0;
        $bCount = 0;
        $cCount = 0;

        for ($i = 0; $i < 9; $i++) {
            $aCount += $dataSets[$i][0] != null ? 1 : 0;
            $bCount += $dataSets[$i][1] != null ? 1 : 0;
            $cCount += $dataSets[$i][2] != null ? 1 : 0;
        }

        $this->assertEquals(3, $aCount);
        $this->assertEquals(3, $bCount);
        $this->assertEquals(3, $cCount);
    }

    public function testWithVariousIterableDataProvidersFromParent(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testFromParent');

        $this->assertEquals([
            ['J'],
            ['K'],
            ['L'],
            ['M'],
            ['N'],
            ['O'],
            ['P'],
            ['Q'],
            ['R'],
        ], $dataSets);
    }

    public function testWithVariousIterableDataProvidersInParent(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testInParent');

        $this->assertEquals([
            ['J'],
            ['K'],
            ['L'],
            ['M'],
            ['N'],
            ['O'],
            ['P'],
            ['Q'],
            ['R'],
        ], $dataSets);
    }

    public function testWithVariousIterableAbstractDataProviders(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testAbstract');

        $this->assertEquals([
            ['S'],
            ['T'],
            ['U'],
            ['V'],
            ['W'],
            ['X'],
            ['Y'],
            ['Z'],
            ['P'],
        ], $dataSets);
    }

    public function testWithVariousIterableStaticDataProviders(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testStatic');

        $this->assertEquals([
            ['A'],
            ['B'],
            ['C'],
            ['D'],
            ['E'],
            ['F'],
            ['G'],
            ['H'],
            ['I'],
        ], $dataSets);
    }

    public function testWithVariousIterableNonStaticDataProviders(): void
    {
        $dataSets = (new DataProvider)->providedData(VariousIterableDataProviderTest::class, 'testNonStatic');

        $this->assertEquals([
            ['S'],
            ['T'],
            ['U'],
            ['V'],
            ['W'],
            ['X'],
            ['Y'],
            ['Z'],
            ['P'],
        ], $dataSets);
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

        $this->assertSame([
            'foo' => ['a', 'b'],
            'bar' => ['c', 'd'],
            0     => ['e', 'f'],
            1     => ['g', 'h'],
        ], $dataSets);
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
}
