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

use SebastianBergmann\Exporter\Exporter as ExporterImplementation;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DefaultExporter implements Exporter
{
    private ExporterImplementation $exporter;

    public function __construct()
    {
        $this->exporter = new ExporterImplementation;
    }

    public function handles(mixed $value): true
    {
        return true;
    }

    public function export(mixed $value): string
    {
        return $this->exporter->export($value);
    }
}
