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

#[CoversClass(GroupCollection::class)]
#[CoversClass(GroupCollectionIterator::class)]
#[UsesClass(Group::class)]
#[Small]
final class GroupCollectionTest extends TestCase
{
    public function testIsCreatedFromArray(): void
    {
        $element  = $this->element();
        $elements = GroupCollection::fromArray([$element]);

        $this->assertSame([$element], $elements->asArray());
        $this->assertFalse($elements->isEmpty());
    }

    public function testIsIterable(): void
    {
        $element  = $this->element();
        $elements = GroupCollection::fromArray([$element]);

        foreach ($elements as $index => $_constant) {
            $this->assertSame(0, $index);
            $this->assertSame($element, $_constant);
        }
    }

    public function testCanBeRepresentedAsArrayOfStrings(): void
    {
        $elements = GroupCollection::fromArray(
            [
                new Group('foo'),
                new Group('bar'),
            ],
        );

        $this->assertSame(['foo', 'bar'], $elements->asArrayOfStrings());
    }

    private function element(): Group
    {
        return new Group('name');
    }
}
