<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\Directory;

#[CoversClass(Xml::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class XmlTest extends TestCase
{
    public function testHasTarget(): void
    {
        $target = new Directory('/path/to/xml-coverage');

        $this->assertSame($target, new Xml($target, true)->target());
    }

    public function testSourceCanBeIncluded(): void
    {
        $this->assertTrue(new Xml(new Directory('/path/to/xml-coverage'), true)->includeSource());
    }

    public function testSourceCanBeExcluded(): void
    {
        $this->assertFalse(new Xml(new Directory('/path/to/xml-coverage'), false)->includeSource());
    }
}
