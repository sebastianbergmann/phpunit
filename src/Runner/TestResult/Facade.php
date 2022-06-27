<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Runner\NoIgnoredEventException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private Collector $collector;

    public function __construct(\PHPUnit\Event\Facade $facade)
    {
        $this->collector = new Collector($facade);
    }

    public function result(): TestResult
    {
        return $this->collector->result();
    }

    /**
     * @psalm-return list<class-string>
     */
    public function passedTestClasses(): array
    {
        return $this->collector->passedTestClasses();
    }

    /**
     * @psalm-return array<string,array{result: mixed, size: TestSize}>
     */
    public function passedTestMethods(): array
    {
        return $this->collector->passedTestMethods();
    }

    public function hasTestErroredEvents(): bool
    {
        return $this->collector->hasTestErroredEvents();
    }

    public function hasTestFailedEvents(): bool
    {
        return $this->collector->hasTestFailedEvents();
    }

    public function hasWarningEvents(): bool
    {
        return $this->collector->hasWarningEvents();
    }

    public function hasTestConsideredRiskyEvents(): bool
    {
        return $this->collector->hasTestConsideredRiskyEvents();
    }

    public function hasTestSkippedEvents(): bool
    {
        return $this->collector->hasTestSkippedEvents();
    }

    public function hasTestMarkedIncompleteEvents(): bool
    {
        return $this->collector->hasTestMarkedIncompleteEvents();
    }

    public function ignoreTestTriggeredDeprecationEventForExpectation(): void
    {
        $this->collector->ignoreTestTriggeredDeprecationEventForExpectation();
    }

    public function ignoreTestTriggeredErrorEventForExpectation(): void
    {
        $this->collector->ignoreTestTriggeredErrorEventForExpectation();
    }

    public function ignoreTestTriggeredNoticeEventForExpectation(): void
    {
        $this->collector->ignoreTestTriggeredNoticeEventForExpectation();
    }

    public function ignoreTestTriggeredWarningEventForExpectation(): void
    {
        $this->collector->ignoreTestTriggeredWarningEventForExpectation();
    }

    public function hasIgnoredEvent(): bool
    {
        return $this->collector->hasIgnoredEvent();
    }

    /**
     * @throws NoIgnoredEventException
     */
    public function ignoredEvent(): DeprecationTriggered|PhpDeprecationTriggered|ErrorTriggered|NoticeTriggered|PhpNoticeTriggered|WarningTriggered|PhpWarningTriggered
    {
        return $this->collector->ignoredEvent();
    }
}
