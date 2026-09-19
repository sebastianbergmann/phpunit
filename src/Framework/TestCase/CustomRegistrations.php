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

use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Exporter\ObjectExporter;

/**
 * Keeps track of the comparators and object exporters that a test registers
 * so that they can be unregistered once the test has finished.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CustomRegistrations
{
    /**
     * @var list<Comparator>
     */
    private array $comparators = [];

    /**
     * @var list<ObjectExporter>
     */
    private array $objectExporters = [];

    public function registerComparator(Comparator $comparator): void
    {
        ComparatorFactory::getInstance()->register($comparator);

        $this->comparators[] = $comparator;
    }

    public function registerObjectExporter(ObjectExporter $objectExporter): void
    {
        Exporter::registerObjectExporter($objectExporter);

        $this->objectExporters[] = $objectExporter;
    }

    public function unregisterAll(): void
    {
        $factory = ComparatorFactory::getInstance();

        foreach ($this->comparators as $comparator) {
            $factory->unregister($comparator);
        }

        $this->comparators = [];

        foreach ($this->objectExporters as $objectExporter) {
            Exporter::unregisterObjectExporter($objectExporter);
        }

        $this->objectExporters = [];
    }
}
