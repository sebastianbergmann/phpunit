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

use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\TestFixture\MultiDependencyTest;

#[CoversClass(DefaultResultCache::class)]
#[Small]
final class DefaultResultCacheTest extends TestCase
{
    public function testGetTimeForNonExistentTestNameReturnsFloatZero(): void
    {
        $this->assertSame(0.0, (new DefaultResultCache)->time('doesNotExist'));
    }

    public function testReadsCacheFromProvidedFilename(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultResultCache($cacheFile);
        $cache->load();

        $this->assertTrue($cache->status(MultiDependencyTest::class . '::testOne')->isUnknown());
        $this->assertTrue($cache->status(MultiDependencyTest::class . '::testFive')->isSkipped());
    }

    public function testDoesClearCacheBeforeLoad(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultResultCache($cacheFile);
        $cache->setStatus('someTest', TestStatus::failure());

        $this->assertTrue($cache->status(MultiDependencyTest::class . '::testFive')->isUnknown());

        $cache->load();

        $this->assertTrue($cache->status(MultiDependencyTest::class . '::someTest')->isUnknown());
        $this->assertTrue($cache->status(MultiDependencyTest::class . '::testFive')->isSkipped());
    }

    public function testCanPersistCacheToFile(): void
    {
        $cacheFile = tempnam(sys_get_temp_dir(), 'phpunit_');
        $cache     = new DefaultResultCache($cacheFile);
        $testName  = 'test' . uniqid('', true);

        $cache->setStatus($testName, TestStatus::skipped());
        $cache->persist();

        $cache = new DefaultResultCache($cacheFile);
        $cache->load();

        $this->assertTrue($cache->status($testName)->isSkipped());

        unlink($cacheFile);
    }

    public function testCanBeMerged(): void
    {
        $cacheSourceOne = new DefaultResultCache;
        $cacheSourceOne->setStatus('status.a', TestStatus::skipped());
        $cacheSourceOne->setStatus('status.b', TestStatus::incomplete());
        $cacheSourceOne->setTime('time.a', 1);
        $cacheSourceOne->setTime('time.b', 2);
        $cacheSourceTwo = new DefaultResultCache;
        $cacheSourceTwo->setStatus('status.c', TestStatus::failure());
        $cacheSourceTwo->setTime('time.c', 4);

        $sum = new DefaultResultCache;
        $sum->mergeWith($cacheSourceOne);

        $this->assertSame(TestStatus::skipped()->asString(), $sum->status('status.a')->asString());
        $this->assertSame(TestStatus::incomplete()->asString(), $sum->status('status.b')->asString());
        $this->assertNotSame(TestStatus::failure()->asString(), $sum->status('status.c')->asString());

        $this->assertSame(1.0, $sum->time('time.a'));
        $this->assertSame(2.0, $sum->time('time.b'));
        $this->assertNotSame(4.0, $sum->time('time.c'));

        $sum->mergeWith($cacheSourceTwo);

        $this->assertSame(TestStatus::failure()->asString(), $sum->status('status.c')->asString());
        $this->assertSame(4.0, $sum->time('time.c'));
    }
}
