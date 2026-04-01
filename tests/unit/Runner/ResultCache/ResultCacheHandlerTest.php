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
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(ResultCacheHandler::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/result-cache')]
final class ResultCacheHandlerTest extends AbstractEventTestCase
{
    public function testMarkedIncompleteRecordsIncompleteStatus(): void
    {
        $cache   = new DefaultResultCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new ResultCacheHandler($cache, new Facade);

        $test  = $this->testValueObject();
        $event = new MarkedIncomplete(
            $this->telemetryInfo(),
            $test,
            ThrowableBuilder::from(new Exception('not yet implemented')),
        );

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));
        $handler->testMarkedIncomplete($event);

        $id = ResultCacheId::fromTest($test);

        $this->assertTrue($cache->status($id)->isIncomplete());
    }

    public function testConsideredRiskyRecordsRiskyStatus(): void
    {
        $cache   = new DefaultResultCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new ResultCacheHandler($cache, new Facade);

        $test  = $this->testValueObject();
        $event = new ConsideredRisky(
            $this->telemetryInfo(),
            $test,
            'This test did not perform any assertions',
        );

        $handler->testConsideredRisky($event);

        $id = ResultCacheId::fromTest($test);

        $this->assertTrue($cache->status($id)->isRisky());
    }

    public function testSkippedRecordsSkippedStatusAndTime(): void
    {
        $cache   = new DefaultResultCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new ResultCacheHandler($cache, new Facade);

        $test = $this->testValueObject();

        $handler->testPrepared(new Prepared($this->telemetryInfo(), $test));

        $event = new Skipped(
            $this->telemetryInfo(),
            $test,
            'skipped for now',
        );

        $handler->testSkipped($event);

        $id = ResultCacheId::fromTest($test);

        $this->assertTrue($cache->status($id)->isSkipped());
    }

    public function testSkippedWithoutPreparedRecordsZeroDuration(): void
    {
        $cache   = new DefaultResultCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-handler-test.cache');
        $handler = new ResultCacheHandler($cache, new Facade);

        $test  = $this->testValueObject();
        $event = new Skipped(
            $this->telemetryInfo(),
            $test,
            'skipped without prepare',
        );

        $handler->testSkipped($event);

        $id = ResultCacheId::fromTest($test);

        $this->assertTrue($cache->status($id)->isSkipped());
        $this->assertSame(0.0, $cache->time($id));
    }
}
