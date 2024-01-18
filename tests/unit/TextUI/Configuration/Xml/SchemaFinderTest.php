<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function count;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

#[CoversClass(SchemaFinder::class)]
#[Small]
final class SchemaFinderTest extends TestCase
{
    public function testListsAvailableSchemas(): void
    {
        $schemas = (new SchemaFinder)->available();

        $this->assertSame((new Version)->series(), $schemas[0]);
        $this->assertSame('8.5', $schemas[count($schemas) - 1]);
    }

    public function testFindsExistingSchema(): void
    {
        $this->assertFileExists((new SchemaFinder)->find((new Version)->series()));
    }

    public function testDoesNotFindNonExistentSchema(): void
    {
        $this->expectException(CannotFindSchemaException::class);

        (new SchemaFinder)->find('0.0');
    }
}
