<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExtensionLoadedFromPhar implements Event
{
    private Telemetry\Info $telemetryInfo;

    /**
     * @var non-empty-string
     */
    private string $filename;

    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var non-empty-string
     */
    private string $version;

    /**
     * @param non-empty-string $filename
     * @param non-empty-string $name
     * @param non-empty-string $version
     */
    public function __construct(Telemetry\Info $telemetryInfo, string $filename, string $name, string $version)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->filename      = $filename;
        $this->name          = $name;
        $this->version       = $version;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    /**
     * @return non-empty-string
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function version(): string
    {
        return $this->version;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return sprintf(
            'Extension Loaded from PHAR (%s %s)',
            $this->name,
            $this->version,
        );
    }
}
