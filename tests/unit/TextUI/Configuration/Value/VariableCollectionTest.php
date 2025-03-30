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

#[CoversClass(VariableCollection::class)]
#[CoversClass(VariableCollectionIterator::class)]
#[UsesClass(Variable::class)]
#[Small]
final class VariableCollectionTest extends TestCase
{
    public function testIsCreatedFromArray(): void
    {
        $element  = $this->element();
        $elements = VariableCollection::fromArray([$element]);

        $this->assertSame([$element], $elements->asArray());
    }

    public function testIsCountable(): void
    {
        $element  = $this->element();
        $elements = VariableCollection::fromArray([$element]);

        $this->assertCount(1, $elements);
    }

    public function testIsIterable(): void
    {
        $element  = $this->element();
        $elements = VariableCollection::fromArray([$element]);

        foreach ($elements as $index => $_Variable) {
            $this->assertSame(0, $index);
            $this->assertSame($element, $_Variable);
        }
    }

    private function element(): Variable
    {
        return new Variable('name', 'value', false);
    }
}
