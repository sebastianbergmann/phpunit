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
use PHPUnit\TextUI\Configuration\File;

#[CoversClass(Crap4j::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class Crap4jTest extends TestCase
{
    public function testHasTarget(): void
    {
        $target = new File('/path/to/crap4j.xml');

        $this->assertSame($target, new Crap4j($target, 30)->target());
    }

    public function testHasThreshold(): void
    {
        $this->assertSame(30, new Crap4j(new File('/path/to/crap4j.xml'), 30)->threshold());
    }
}
