<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Exporter\ExportContext;
use SebastianBergmann\Exporter\Exporter as OriginalExporter;
use SebastianBergmann\Exporter\ObjectExporter;
use stdClass;

#[CoversClass(CustomRegistrations::class)]
#[Small]
final class CustomRegistrationsTest extends TestCase
{
    public function testRegisteredComparatorIsUsedUntilItIsUnregistered(): void
    {
        $registrations = new CustomRegistrations;
        $comparator    = $this->createStub(Comparator::class);

        $comparator->method('accepts')->willReturn(true);

        $registrations->registerComparator($comparator);

        $this->assertSame($comparator, ComparatorFactory::getInstance()->getComparatorFor(1, 1));

        $registrations->unregisterAll();

        $this->assertNotSame($comparator, ComparatorFactory::getInstance()->getComparatorFor(1, 1));
    }

    public function testRegisteredObjectExporterIsUsedUntilItIsUnregistered(): void
    {
        $registrations  = new CustomRegistrations;
        $objectExporter = new class implements ObjectExporter
        {
            public function handles(object $object): bool
            {
                return $object instanceof stdClass;
            }

            public function export(object $object, OriginalExporter $exporter, int $indentation, ExportContext $context): string
            {
                return 'CUSTOM';
            }
        };

        $registrations->registerObjectExporter($objectExporter);

        $this->assertSame('CUSTOM', Exporter::export(new stdClass));

        $registrations->unregisterAll();

        $this->assertStringStartsWith('stdClass Object #', Exporter::export(new stdClass));
    }
}
