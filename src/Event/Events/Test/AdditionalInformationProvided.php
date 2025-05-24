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

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class AdditionalInformationProvided implements Event
{
    private Telemetry\Info $telemetryInfo;
    private TestMethod $test;

    /**
     * @var non-empty-string
     */
    private string $additionalInformation;

    /**
     * @param non-empty-string $additionalInformation
     */
    public function __construct(Telemetry\Info $telemetryInfo, TestMethod $test, string $additionalInformation)
    {
        $this->telemetryInfo         = $telemetryInfo;
        $this->test                  = $test;
        $this->additionalInformation = $additionalInformation;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function test(): TestMethod
    {
        return $this->test;
    }

    /**
     * @return non-empty-string
     */
    public function additionalInformation(): string
    {
        return $this->additionalInformation;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return sprintf(
            'Test Provided Additional Information%s%s',
            PHP_EOL,
            $this->additionalInformation,
        );
    }
}
