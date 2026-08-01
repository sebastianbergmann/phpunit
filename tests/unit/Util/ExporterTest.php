<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Exporter\ExportContext;
use SebastianBergmann\Exporter\Exporter as OriginalExporter;
use SebastianBergmann\Exporter\ObjectExporter;
use stdClass;

#[CoversClass(Exporter::class)]
#[Small]
final class ExporterTest extends TestCase
{
    /**
     * @var list<ObjectExporter>
     */
    private array $objectExporters = [];

    protected function tearDown(): void
    {
        foreach ($this->objectExporters as $objectExporter) {
            Exporter::unregisterObjectExporter($objectExporter);
        }

        $this->objectExporters = [];
    }

    public function testObjectExporterRegisteredLastTakesPrecedence(): void
    {
        $this->registerObjectExporterThatExports('FIRST');
        $this->registerObjectExporterThatExports('SECOND');

        $this->assertSame('SECOND', Exporter::export(new stdClass));
    }

    public function testUnregisteringObjectExporterKeepsThoseRegisteredBeforeIt(): void
    {
        $this->registerObjectExporterThatExports('FIRST');

        $second = $this->registerObjectExporterThatExports('SECOND');

        Exporter::unregisterObjectExporter($second);

        $this->assertSame('FIRST', Exporter::export(new stdClass));
    }

    public function testUnregisteringObjectExporterThatIsNotRegisteredDoesNotChangeAnything(): void
    {
        $this->registerObjectExporterThatExports('FIRST');

        Exporter::unregisterObjectExporter($this->objectExporterThatExports('SECOND'));

        $this->assertSame('FIRST', Exporter::export(new stdClass));
    }

    public function testDefaultExportIsUsedAfterAllObjectExportersHaveBeenUnregistered(): void
    {
        $objectExporter = $this->registerObjectExporterThatExports('FIRST');

        Exporter::unregisterObjectExporter($objectExporter);

        $this->assertStringStartsWith('stdClass Object #', Exporter::export(new stdClass));
    }

    private function registerObjectExporterThatExports(string $representation): ObjectExporter
    {
        $objectExporter = $this->objectExporterThatExports($representation);

        Exporter::registerObjectExporter($objectExporter);

        $this->objectExporters[] = $objectExporter;

        return $objectExporter;
    }

    private function objectExporterThatExports(string $representation): ObjectExporter
    {
        return new class($representation) implements ObjectExporter
        {
            private string $representation;

            public function __construct(string $representation)
            {
                $this->representation = $representation;
            }

            public function handles(object $object): bool
            {
                return $object instanceof stdClass;
            }

            public function export(object $object, OriginalExporter $exporter, int $indentation, ExportContext $context): string
            {
                return $this->representation;
            }
        };
    }
}
