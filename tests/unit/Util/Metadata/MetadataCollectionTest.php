<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Util\Metadata\Metadata
 * @covers \PHPUnit\Util\Metadata\MetadataCollection
 * @covers \PHPUnit\Util\Metadata\MetadataCollectionIterator
 *
 * @uses \PHPUnit\Util\Metadata\Metadata
 * @uses \PHPUnit\Util\Metadata\Test
 */
final class MetadataCollectionTest extends TestCase
{
    public function testCanBeEmpty(): void
    {
        $collection = MetadataCollection::fromArray([]);

        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());
    }

    public function testCanBeCreatedFromArray(): void
    {
        $metadata = new Test;

        $collection = MetadataCollection::fromArray([$metadata]);

        $this->assertContains($metadata, $collection);
    }

    public function testIsCountable(): void
    {
        $metadata = new Test;

        $collection = MetadataCollection::fromArray([$metadata]);

        $this->assertCount(1, $collection);
        $this->assertFalse($collection->isEmpty());
    }

    public function testIsIterable(): void
    {
        $metadata = new Test;

        foreach (MetadataCollection::fromArray([$metadata]) as $key => $value) {
            $this->assertSame(0, $key);
            $this->assertSame($metadata, $value);
        }
    }
}
