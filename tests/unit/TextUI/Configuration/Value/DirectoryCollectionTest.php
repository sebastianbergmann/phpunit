<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryCollection::class)]
#[CoversClass(DirectoryCollectionIterator::class)]
#[UsesClass(Directory::class)]
#[Small]
final class DirectoryCollectionTest extends TestCase
{
    public function testIsCreatedFromArray(): void
    {
        $element  = $this->element();
        $elements = DirectoryCollection::fromArray([$element]);

        $this->assertSame([$element], $elements->asArray());
    }

    public function testIsCountable(): void
    {
        $element  = $this->element();
        $elements = DirectoryCollection::fromArray([$element]);

        $this->assertCount(1, $elements);
        $this->assertFalse($elements->isEmpty());
    }

    public function testIsIterable(): void
    {
        $element  = $this->element();
        $elements = DirectoryCollection::fromArray([$element]);

        foreach ($elements as $index => $_constant) {
            $this->assertSame(0, $index);
            $this->assertSame($element, $_constant);
        }
    }

    private function element(): Directory
    {
        return new Directory('path');
    }
}
