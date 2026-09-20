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
use PHPUnit\Event\Facade;
use PHPUnit\Event\TestRunner\ErrorTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(TestRunnerTriggeredIssueErrorSubscriber::class)]
#[UsesClass(Collector::class)]
#[Small]
#[Group('test-runner')]
final class TestRunnerTriggeredIssueErrorSubscriberTest extends AbstractEventTestCase
{
    public function testForwardsEventToCollector(): void
    {
        $collector = new Collector(new Facade, new IssueFilter($this->source()));
        $event     = new ErrorTriggered($this->telemetryInfo(), 'message', 'file.php', 1, false);

        new TestRunnerTriggeredIssueErrorSubscriber($collector)->notify($event);

        $this->assertSame([$event], $collector->result()->testRunnerTriggeredIssueErrorEvents());
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
