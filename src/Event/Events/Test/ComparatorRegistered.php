<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use SebastianBergmann\Comparator\Comparator;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ComparatorRegistered implements Event
{
    private Telemetry\Info $telemetryInfo;

    /**
     * @var class-string<Comparator>
     */
    private string $className;

    /**
     * @param class-string<Comparator> $className
     */
    public function __construct(Telemetry\Info $telemetryInfo, string $className)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->className     = $className;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    /**
     * @return class-string<Comparator>
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return sprintf(
            'Comparator Registered (%s)',
            $this->className,
        );
    }
}
