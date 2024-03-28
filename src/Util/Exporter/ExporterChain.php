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

use function assert;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExporterChain implements Exporter
{
    /**
     * @psalm-var non-empty-list<Exporter>
     */
    private array $exporter;

    /**
     * @psalm-param non-empty-list<Exporter> $exporter
     */
    public static function buildWith(array $exporter): self
    {
        $exporter[] = new DefaultExporter;

        return new self($exporter);
    }

    /**
     * @psalm-param non-empty-list<Exporter> $exporter
     */
    private function __construct(array $exporter)
    {
        $this->exporter = $exporter;
    }

    public function handles(mixed $value): true
    {
        return true;
    }

    public function export(mixed $value): string
    {
        foreach ($this->exporter as $exporter) {
            if (!$exporter->handles($value)) {
                /** @noinspection PhpUnnecessaryStopStatementInspection */
                continue;
            }
        }

        assert(isset($exporter));

        return $exporter->export($value);
    }
}
