<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Xml;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

/**
 * @small
 *
 * @covers \PHPUnit\Util\Xml\SchemaFinder
 */
final class SchemaFinderTest extends TestCase
{
    public function testFindsExistingSchemaForComposerInstallation(): void
    {
        $this->assertFileExists((new SchemaFinder)->find((new Version)->series()));
    }

    public function testDoesNotFindNonExistentSchemaForComposerInstallation(): void
    {
        $this->expectException(Exception::class);

        (new SchemaFinder)->find('0.0');
    }
}
