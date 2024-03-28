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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExporterFacade
{
    private static ?self $instance = null;
    private Exporter $exporter;

    public static function instance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    private function __construct()
    {
        $this->exporter = new DefaultExporter;
    }

    public function export(mixed $value): string
    {
        return $this->exporter->export($value);
    }

    public function use(Exporter $exporter): void
    {
        $this->exporter = $exporter;
    }
}
