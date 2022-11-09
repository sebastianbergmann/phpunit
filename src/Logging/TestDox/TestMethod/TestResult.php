<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @psalm-immutable
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult
{
    private readonly TestMethod $test;
    private readonly Duration $duration;
    private readonly TestStatus $status;
    private readonly ?Throwable $throwable;

    /**
     * @psalm-var list<class-string|trait-string>
     */
    private readonly array $testDoubles;

    /**
     * @psalm-param  list<class-string|trait-string> $testDoubles
     */
    public function __construct(TestMethod $test, Duration $duration, TestStatus $status, ?Throwable $throwable, array $testDoubles)
    {
        $this->test        = $test;
        $this->duration    = $duration;
        $this->status      = $status;
        $this->throwable   = $throwable;
        $this->testDoubles = $testDoubles;
    }

    public function test(): TestMethod
    {
        return $this->test;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }

    public function status(): TestStatus
    {
        return $this->status;
    }

    /**
     * @psalm-assert-if-true !null $this->throwable
     */
    public function hasThrowable(): bool
    {
        return $this->throwable !== null;
    }

    public function throwable(): ?Throwable
    {
        return $this->throwable;
    }

    /**
     * @psalm-return list<class-string|trait-string>
     */
    public function testDoubles(): array
    {
        return $this->testDoubles;
    }
}
