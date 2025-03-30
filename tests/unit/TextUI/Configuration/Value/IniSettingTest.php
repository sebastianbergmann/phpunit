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
use PHPUnit\Framework\TestCase;

#[CoversClass(IniSetting::class)]
#[Small]
final class IniSettingTest extends TestCase
{
    public function testHasName(): void
    {
        $this->assertSame('name', (new IniSetting('name', 'value'))->name());
    }

    public function testHasValue(): void
    {
        $this->assertSame('value', (new IniSetting('name', 'value'))->value());
    }
}
