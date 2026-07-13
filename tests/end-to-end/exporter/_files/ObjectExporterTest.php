<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ObjectExporter;

use PHPUnit\Framework\TestCase;
use stdClass;

final class ObjectExporterTest extends TestCase
{
    public function testCustomObjectExporterIsUsedForConstraintFailureDescription(): void
    {
        $this->registerObjectExporter(new MessageExporter);

        $this->assertContains(new Message('hello'), [new Message('goodbye')]);
    }

    public function testCustomObjectExporterIsUsedForComparisonFailure(): void
    {
        $this->registerObjectExporter(new MessageExporter);

        $this->assertEquals(new Message('hello'), new stdClass);
    }

    public function testDefaultExportIsUsedWhenNoObjectExporterIsRegistered(): void
    {
        $this->assertContains(new Message('hello'), [new Message('goodbye')]);
    }
}
