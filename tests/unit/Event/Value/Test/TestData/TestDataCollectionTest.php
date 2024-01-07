<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestData;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestDataCollection::class)]
#[CoversClass(TestDataCollectionIterator::class)]
#[UsesClass(TestData::class)]
#[UsesClass(DataFromDataProvider::class)]
#[UsesClass(DataFromTestDependency::class)]
#[Small]
final class TestDataCollectionTest extends TestCase
{
    public function testMayBeEmpty(): void
    {
        $collection = TestDataCollection::fromArray([]);

        $this->assertCount(0, $collection);
        $this->assertFalse($collection->hasDataFromDataProvider());
    }

    public function testMayContainDataFromDataProvider(): void
    {
        $data       = $this->dataFromDataProvider();
        $collection = TestDataCollection::fromArray([$data]);

        $this->assertTrue($collection->hasDataFromDataProvider());
        $this->assertSame([$data], $collection->asArray());
        $this->assertSame($data, $collection->dataFromDataProvider());
    }

    public function testMayContainDataFromDependedUponTest(): void
    {
        $data       = $this->dataFromDependedUponTest();
        $collection = TestDataCollection::fromArray([$data]);

        $this->assertFalse($collection->hasDataFromDataProvider());
        $this->assertSame([$data], $collection->asArray());
    }

    public function testExceptionIsRaisedWhenDataFromDataProviderIsAccessedButDoesNotExist(): void
    {
        $collection = TestDataCollection::fromArray([]);

        $this->expectException(NoDataSetFromDataProviderException::class);

        $collection->dataFromDataProvider();
    }

    public function testIsIterable(): void
    {
        $data       = $this->dataFromDataProvider();
        $collection = TestDataCollection::fromArray([$data]);

        foreach ($collection as $index => $element) {
            $this->assertSame(0, $index);
            $this->assertSame($data, $element);
        }
    }

    private function dataFromDataProvider(): DataFromDataProvider
    {
        return DataFromDataProvider::from(
            'data-set-name',
            'data-as-string',
            'data-as-string-for-output',
        );
    }

    private function dataFromDependedUponTest(): DataFromTestDependency
    {
        return DataFromTestDependency::from('data-as-string');
    }
}
