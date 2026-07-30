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

final class ObjectExporterPrecedenceTest extends AbstractMessageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerObjectExporter(new AlternativeMessageExporter);
    }

    public function testMostRecentlyRegisteredObjectExporterTakesPrecedence(): void
    {
        $this->assertContains(new Message('hello'), []);
    }

    public function testPreviouslyRegisteredObjectExporterIsUsedForObjectsTheMostRecentlyRegisteredOneDoesNotHandle(): void
    {
        $this->assertContains(new Message('goodbye'), []);
    }
}
