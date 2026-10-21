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

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PropertyFalsified;
use PHPUnit\Event\TestRunner\PhpunitNoticeTriggered as TestRunnerPhpunitNoticeTriggered;
use PHPUnit\Event\TestRunner\TimeLimitExceeded;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(TestResult::class)]
#[UsesClass(Collector::class)]
#[Small]
#[Group('test-runner')]
final class TestResultTest extends AbstractEventTestCase
{
    public function testHasNoTestRunnerTriggeredNoticeEventsByDefault(): void
    {
        $result = $this->collector()->result();

        $this->assertSame([], $result->testRunnerTriggeredNoticeEvents());
        $this->assertSame(0, $result->numberOfTestRunnerTriggeredNoticeEvents());
    }

    public function testProvidesTestRunnerTriggeredNoticeEvents(): void
    {
        $collector = $this->collector();
        $event     = new TestRunnerPhpunitNoticeTriggered($this->telemetryInfo(), 'message');

        $collector->testRunnerTriggeredPhpunitNotice($event);

        $result = $collector->result();

        $this->assertSame([$event], $result->testRunnerTriggeredNoticeEvents());
        $this->assertSame(1, $result->numberOfTestRunnerTriggeredNoticeEvents());
    }

    public function testTimeLimitIsNotExceededByDefault(): void
    {
        $this->assertFalse($this->collector()->result()->wasTimeLimitExceeded());
    }

    public function testProvidesTimeLimitExceededEventWhenTimeLimitWasExceeded(): void
    {
        $collector = $this->collector();
        $event     = new TimeLimitExceeded($this->telemetryInfo(), 60);

        $collector->testRunnerTimeLimitExceeded($event);

        $result = $collector->result();

        $this->assertTrue($result->wasTimeLimitExceeded());
        $this->assertSame($event, $result->timeLimitExceededEvent());
    }

    public function testDoesNotProvideHowATestWasFalsifiedByDefault(): void
    {
        $this->assertNull($this->collector()->result()->propertyFalsifiedEventFor($this->testValueObject()));
    }

    public function testProvidesHowAPropertyWasFalsified(): void
    {
        $collector = $this->collector();
        $event     = new PropertyFalsified($this->telemetryInfo(), $this->testValueObject(), 1234, 2, 3, 1, false, false, [0], ['value' => '0'], null, [], [], [], 0, 0);

        $collector->testPropertyFalsified($event);

        $this->assertSame($event, $collector->result()->propertyFalsifiedEventFor($this->testValueObject()));
    }

    public function testHasNoPhpOrUserDeprecationsByDefault(): void
    {
        $this->assertSame(0, $this->collector()->result()->numberOfPhpOrUserDeprecations());
    }

    public function testCountsPhpDeprecationAsPhpOrUserDeprecation(): void
    {
        $collector = $this->collector();

        $collector->testTriggeredPhpDeprecation(
            new PhpDeprecationTriggered(
                $this->telemetryInfo(),
                $this->testValueObject(),
                'message',
                'file.php',
                1,
                false,
                false,
                false,
                false,
                IssueTrigger::from(null, null),
            ),
        );

        $this->assertSame(1, $collector->result()->numberOfPhpOrUserDeprecations());
    }

    private function collector(): Collector
    {
        return new Collector(
            new Facade,
            new IssueFilter($this->source()),
        );
    }

    private function source(): Source
    {
        return new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            [
                'functions'               => [],
                'methods'                 => [],
                'ignoreUndefinedTriggers' => true,
            ],
            false,
            false,
            false,
            false,
        );
    }
}
