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

#[CoversClass(ExtensionBootstrapCollection::class)]
#[CoversClass(ExtensionBootstrapCollectionIterator::class)]
#[UsesClass(ExtensionBootstrap::class)]
#[Small]
final class ExtensionBootstrapCollectionTest extends TestCase
{
    public function testIsCreatedFromArray(): void
    {
        $element  = $this->element();
        $elements = ExtensionBootstrapCollection::fromArray([$element]);

        $this->assertSame([$element], $elements->asArray());
    }

    public function testIsIterable(): void
    {
        $element  = $this->element();
        $elements = ExtensionBootstrapCollection::fromArray([$element]);

        foreach ($elements as $index => $_constant) {
            $this->assertSame(0, $index);
            $this->assertSame($element, $_constant);
        }
    }

    private function element(): ExtensionBootstrap
    {
        return new ExtensionBootstrap('ClassName', []);
    }
}
