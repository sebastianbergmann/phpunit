<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Parser\Parser as ParserInterface;

#[CoversClass(Registry::class)]
#[Small]
#[Group('metadata')]
final class RegistryTest extends TestCase
{
    public function testParserReturnsParserInstanceAndIsSingleton(): void
    {
        $first = Registry::parser();

        $this->assertInstanceOf(ParserInterface::class, $first);

        $second = Registry::parser();

        // Same instance (singleton)
        $this->assertSame($first, $second);
    }

    public function testParserRecreatedAfterResettingInstanceWithReflection(): void
    {
        $first = Registry::parser();

        // Reset the private static instance using reflection to simulate a fresh process
        $ref = new \ReflectionClass(Registry::class);
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);

        $second = Registry::parser();

        $this->assertInstanceOf(ParserInterface::class, $second);
        $this->assertNotSame($first, $second);
    }
}
