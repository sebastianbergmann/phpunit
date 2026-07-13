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

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class ObjectExporterIsolationTest extends TestCase
{
    public function testCustomObjectExporterIsUsedInSeparateProcess(): void
    {
        $this->registerObjectExporter(new MessageExporter);

        $this->assertContains(new Message('hello'), [new Message('goodbye')]);
    }
}
