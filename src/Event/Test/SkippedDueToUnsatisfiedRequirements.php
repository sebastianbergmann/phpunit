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

use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

final class SkippedDueToUnsatisfiedRequirements implements Event
{
    private Telemetry\Info $telemetryInfo;

    private Code\ClassMethod $testMethod;

    /**
     * @psalm-var list<string>
     *
     * @var array<int, string>
     */
    private array $missingRequirements;

    public function __construct(
        Telemetry\Info $telemetryInfo,
        Code\ClassMethod $testMethod,
        string ...$missingRequirements
    ) {
        $this->telemetryInfo       = $telemetryInfo;
        $this->testMethod          = $testMethod;
        $this->missingRequirements = $missingRequirements;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testMethod(): Code\ClassMethod
    {
        return $this->testMethod;
    }

    /**
     * @psalm-return list<string>
     *
     * @return array<int, string>
     */
    public function missingRequirements(): array
    {
        return $this->missingRequirements;
    }

    public function asString(): string
    {
        return '';
    }
}
