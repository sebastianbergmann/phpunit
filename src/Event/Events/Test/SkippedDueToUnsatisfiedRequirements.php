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
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class SkippedDueToUnsatisfiedRequirements implements Event
{
    private Telemetry\Info $telemetryInfo;

    private Code\TestMethod $testMethod;

    /**
     * @psalm-var list<string>
     */
    private array $missingRequirements;

    public function __construct(Telemetry\Info $telemetryInfo, Code\TestMethod $testMethod, string ...$missingRequirements)
    {
        $this->telemetryInfo       = $telemetryInfo;
        $this->testMethod          = $testMethod;
        $this->missingRequirements = $missingRequirements;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testMethod(): Code\TestMethod
    {
        return $this->testMethod;
    }

    /**
     * @psalm-return list<string>
     */
    public function missingRequirements(): array
    {
        return $this->missingRequirements;
    }

    public function asString(): string
    {
        $message = PHP_EOL;

        foreach ($this->missingRequirements as $missingRequirement) {
            $message .= $missingRequirement . PHP_EOL;
        }

        return sprintf(
            'Test Skipped Due To Unsatisfied Requirements (%s::%s)%s',
            $this->testMethod->className(),
            $this->testMethod->methodName(),
            $message
        );
    }
}
