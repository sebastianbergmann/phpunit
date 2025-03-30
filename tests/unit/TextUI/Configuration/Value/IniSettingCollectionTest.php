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

#[CoversClass(IniSettingCollection::class)]
#[CoversClass(IniSettingCollectionIterator::class)]
#[UsesClass(IniSetting::class)]
#[Small]
final class IniSettingCollectionTest extends TestCase
{
    public function testIsCreatedFromArray(): void
    {
        $element  = $this->element();
        $elements = IniSettingCollection::fromArray([$element]);

        $this->assertSame([$element], $elements->asArray());
    }

    public function testIsCountable(): void
    {
        $element  = $this->element();
        $elements = IniSettingCollection::fromArray([$element]);

        $this->assertCount(1, $elements);
    }

    public function testIsIterable(): void
    {
        $element  = $this->element();
        $elements = IniSettingCollection::fromArray([$element]);

        foreach ($elements as $index => $_IniSetting) {
            $this->assertSame(0, $index);
            $this->assertSame($element, $_IniSetting);
        }
    }

    private function element(): IniSetting
    {
        return new IniSetting('name', 'value');
    }
}
