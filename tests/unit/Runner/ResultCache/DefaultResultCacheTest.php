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
}
