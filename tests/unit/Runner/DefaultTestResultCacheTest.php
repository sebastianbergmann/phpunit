<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;
use MultiDependencyTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Runner\DefaultTestResultCache
 * @small
 */
final class DefaultTestResultCacheTest extends TestCase
{
    public function testGetTimeForNonExistentTestNameReturnsFloatZero(): void
    {
        $this->assertSame(0.0, (new DefaultTestResultCache)->getTime('doesNotExist'));
    }

    public function testReadsCacheFromProvidedFilename(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultTestResultCache($cacheFile);
        $cache->load();

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState(MultiDependencyTest::class . '::testOne'));
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $cache->getState(MultiDependencyTest::class . '::testFive'));
    }

    public function testDoesClearCacheBeforeLoad(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultTestResultCache($cacheFile);
        $cache->setState('someTest', BaseTestRunner::STATUS_FAILURE);

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState(MultiDependencyTest::class . '::testFive'));

        $cache->load();

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState(MultiDependencyTest::class . '::someTest'));
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $cache->getState(MultiDependencyTest::class . '::testFive'));
    }

    public function testCanPersistCacheToFile(): void
    {
        $cacheFile = tempnam(sys_get_temp_dir(), 'phpunit_');
        $cache     = new DefaultTestResultCache($cacheFile);
        $testName  = 'test' . uniqid('', true);

        $cache->setState($testName, BaseTestRunner::STATUS_SKIPPED);
        $cache->persist();

        $cache = new DefaultTestResultCache($cacheFile);
        $cache->load();

        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $cache->getState($testName));

        unlink($cacheFile);
    }
}
