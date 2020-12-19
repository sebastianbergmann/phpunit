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

final class BeforeFirstTestMethodFinished implements Event
{
    private Telemetry\Info$telemetryInfo;

    /**
     * @psalm-var class-string
     */
    private string $testClassName;

    /**
     * @psalm-var list<Code\ClassMethod>
     *
     * @var array<int, Code\ClassMethod>
     */
    private $calledMethods;

    /**
     * @psalm-param class-string $testClassName
     */
    public function __construct(
        Telemetry\Info $telemetryInfo,
        string $testClassName,
        Code\ClassMethod ...$calledMethods
    ) {
        $this->telemetryInfo = $telemetryInfo;
        $this->testClassName = $testClassName;
        $this->calledMethods = $calledMethods;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    /**
     * @psalm-return class-string
     */
    public function testClassName(): string
    {
        return $this->testClassName;
    }

    /**
     * @psalm-return list<Code\ClassMethod>
     *
     * @return array<int, Code\ClassMethod>
     */
    public function calledMethods(): array
    {
        return $this->calledMethods;
    }
}
