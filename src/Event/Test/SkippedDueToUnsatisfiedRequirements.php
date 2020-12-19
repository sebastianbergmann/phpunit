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

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use SebastianBergmann\CodeUnit;

final class SkippedDueToUnsatisfiedRequirements implements Event
{
    private Telemetry\Info $telemetryInfo;

    private string $testClassName;

    private CodeUnit\ClassMethodUnit $testMethodName;

    /**
     * @psalm-var list<string>
     *
     * @var array<int, string>
     */
    private array $missingRequirements;

    public function __construct(
        Telemetry\Info $telemetryInfo,
        string $testClassName,
        CodeUnit\ClassMethodUnit $testMethodName,
        string ...$missingRequirements
    ) {
        $this->telemetryInfo       = $telemetryInfo;
        $this->testClassName       = $testClassName;
        $this->testMethodName      = $testMethodName;
        $this->missingRequirements = $missingRequirements;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function testClassName(): string
    {
        return $this->testClassName;
    }

    public function testMethodName(): CodeUnit\ClassMethodUnit
    {
        return $this->testMethodName;
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
}
