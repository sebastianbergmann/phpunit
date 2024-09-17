<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Runtime;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyHook::class)]
#[CoversClass(PropertyGetHook::class)]
#[Small]
#[Group('test-doubles')]
final class PropertyGetHookTest extends TestCase
{
    public function testHasPropertyName(): void
    {
        $propertyName = 'property';

        $propertyHook = PropertyHook::get($propertyName);

        $this->assertSame($propertyName, $propertyHook->propertyName());
    }

    public function testCanBeRepresentedAsString(): void
    {
        $propertyName = 'property';

        $propertyHook = PropertyHook::get($propertyName);

        $this->assertSame('$' . $propertyName . '::get', $propertyHook->asString());
    }
}
