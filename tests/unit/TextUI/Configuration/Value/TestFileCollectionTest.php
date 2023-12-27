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
use PHPUnit\Util\VersionComparisonOperator;

#[CoversClass(TestFileCollection::class)]
#[CoversClass(TestFileCollectionIterator::class)]
#[UsesClass(TestFile::class)]
#[Small]
final class TestFileCollectionTest extends TestCase
{
    public function testIsCreatedFromArray(): void
    {
        $element  = $this->element();
        $elements = TestFileCollection::fromArray([$element]);

        $this->assertSame([$element], $elements->asArray());
    }

    public function testIsCountable(): void
    {
        $element  = $this->element();
        $elements = TestFileCollection::fromArray([$element]);

        $this->assertCount(1, $elements);
        $this->assertFalse($elements->isEmpty());
    }

    public function testIsIterable(): void
    {
        $element  = $this->element();
        $elements = TestFileCollection::fromArray([$element]);

        foreach ($elements as $index => $_constant) {
            $this->assertSame(0, $index);
            $this->assertSame($element, $_constant);
        }
    }

    private function element(): TestFile
    {
        return new TestFile(
            'path',
            '8.2.0',
            new VersionComparisonOperator('>='),
            [],
        );
    }
}
