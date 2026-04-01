<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ResultCache;

use const DIRECTORY_SEPARATOR;
use function sys_get_temp_dir;
use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(TestMarkedIncompleteSubscriber::class)]
#[CoversClass(TestConsideredRiskySubscriber::class)]
#[CoversClass(TestSkippedSubscriber::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/result-cache')]
final class SubscriberTest extends AbstractEventTestCase
{
    public function testMarkedIncompleteSubscriberForwardsToHandler(): void
    {
        $cache   = new DefaultResultCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-subscriber-test.cache');
        $handler = new ResultCacheHandler($cache, new Facade);

        $test  = $this->testValueObject();
        $event = new MarkedIncomplete(
            $this->telemetryInfo(),
            $test,
            ThrowableBuilder::from(new Exception('incomplete')),
        );

        $subscriber = new TestMarkedIncompleteSubscriber($handler);
        $subscriber->notify($event);

        $this->assertTrue($cache->status(ResultCacheId::fromTest($test))->isIncomplete());
    }

    public function testConsideredRiskySubscriberForwardsToHandler(): void
    {
        $cache   = new DefaultResultCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-subscriber-test.cache');
        $handler = new ResultCacheHandler($cache, new Facade);

        $test  = $this->testValueObject();
        $event = new ConsideredRisky(
            $this->telemetryInfo(),
            $test,
            'This test did not perform any assertions',
        );

        $subscriber = new TestConsideredRiskySubscriber($handler);
        $subscriber->notify($event);

        $this->assertTrue($cache->status(ResultCacheId::fromTest($test))->isRisky());
    }

    public function testSkippedSubscriberForwardsToHandler(): void
    {
        $cache   = new DefaultResultCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-subscriber-test.cache');
        $handler = new ResultCacheHandler($cache, new Facade);

        $test  = $this->testValueObject();
        $event = new Skipped(
            $this->telemetryInfo(),
            $test,
            'not applicable',
        );

        $subscriber = new TestSkippedSubscriber($handler);
        $subscriber->notify($event);

        $this->assertTrue($cache->status(ResultCacheId::fromTest($test))->isSkipped());
    }
}
