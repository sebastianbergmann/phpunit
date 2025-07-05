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
        $this->assertSame(0.0, (new DefaultResultCache)->time(ResultCacheId::fromTestClassAndMethodName(self::class, 'doesNotExist')));
    }

    public function testReadsCacheFromProvidedFilename(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultResultCache($cacheFile);
        $cache->load();

        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, 'testOne'))->isUnknown());
        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, 'testFive'))->isSkipped());
    }

    public function testDoesClearCacheBeforeLoad(): void
    {
        $cacheFile     = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache         = new DefaultResultCache($cacheFile);
        $resultCacheId = ResultCacheId::fromTestClassAndMethodName(self::class, 'someTest');
        $cache->setStatus($resultCacheId, TestStatus::failure());

        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, 'testFive'))->isUnknown());

        $cache->load();

        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, 'someTest'))->isUnknown());
        $this->assertTrue($cache->status(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, 'testFive'))->isSkipped());
    }

    public function testCanPersistCacheToFile(): void
    {
        $cacheFile     = tempnam(sys_get_temp_dir(), 'phpunit_');
        $cache         = new DefaultResultCache($cacheFile);
        $resultCacheId = ResultCacheId::fromTestClassAndMethodName(self::class, 'test' . uniqid('', true));

        $cache->setStatus($resultCacheId, TestStatus::skipped());
        $cache->persist();

        $cache = new DefaultResultCache($cacheFile);
        $cache->load();

        $this->assertTrue($cache->status($resultCacheId)->isSkipped());

        unlink($cacheFile);
    }

    public function testCanBeMerged(): void
    {
        $cacheSourceOne = new DefaultResultCache;
        $cacheSourceOne->setStatus(ResultCacheId::fromTestClassAndMethodName(self::class, 'status.a'), TestStatus::skipped());
        $cacheSourceOne->setStatus(ResultCacheId::fromTestClassAndMethodName(self::class, 'status.b'), TestStatus::incomplete());
        $cacheSourceOne->setTime(ResultCacheId::fromTestClassAndMethodName(self::class, 'time.a'), 1);
        $cacheSourceOne->setTime(ResultCacheId::fromTestClassAndMethodName(self::class, 'time.b'), 2);
        $cacheSourceTwo = new DefaultResultCache;
        $cacheSourceTwo->setStatus(ResultCacheId::fromTestClassAndMethodName(self::class, 'status.c'), TestStatus::failure());
        $cacheSourceTwo->setTime(ResultCacheId::fromTestClassAndMethodName(self::class, 'time.c'), 4);

        $sum = new DefaultResultCache;
        $sum->mergeWith($cacheSourceOne);

        $this->assertSame(TestStatus::skipped()->asString(), $sum->status(ResultCacheId::fromTestClassAndMethodName(self::class, 'status.a'))->asString());
        $this->assertSame(TestStatus::incomplete()->asString(), $sum->status(ResultCacheId::fromTestClassAndMethodName(self::class, 'status.b'))->asString());
        $this->assertNotSame(TestStatus::failure()->asString(), $sum->status(ResultCacheId::fromTestClassAndMethodName(self::class, 'status.c'))->asString());

        $this->assertSame(1.0, $sum->time(ResultCacheId::fromTestClassAndMethodName(self::class, 'time.a')));
        $this->assertSame(2.0, $sum->time(ResultCacheId::fromTestClassAndMethodName(self::class, 'time.b')));
        $this->assertNotSame(4.0, $sum->time(ResultCacheId::fromTestClassAndMethodName(self::class, 'time.c')));

        $sum->mergeWith($cacheSourceTwo);

        $this->assertSame(TestStatus::failure()->asString(), $sum->status(ResultCacheId::fromTestClassAndMethodName(self::class, 'status.c'))->asString());
        $this->assertSame(4.0, $sum->time(ResultCacheId::fromTestClassAndMethodName(self::class, 'time.c')));
    }
}
