<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\TestFixture\ObjectExporter\BootstrapMessageExporter;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\Exporter\ObjectExporterChain;

require __DIR__ . '/Message.php';

require __DIR__ . '/MessageExporter.php';

require __DIR__ . '/BootstrapMessageExporter.php';

ComparatorFactory::getInstance()->setExporter(
    new Exporter(0, 40, new ObjectExporterChain([new BootstrapMessageExporter])),
);
