<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\DefaultTestResultCache;

/**
 * @group test-reorder
 * @small
 */
final class TestResultCacheTest extends TestCase
{
    public function testReadsCacheFromProvidedFilename(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultTestResultCache($cacheFile);
        $cache->load();

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState(\MultiDependencyTest::class . '::testOne'));
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $cache->getState(\MultiDependencyTest::class . '::testFive'));
    }

    public function testDoesClearCacheBeforeLoad(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultTestResultCache($cacheFile);
        $cache->setState('someTest', BaseTestRunner::STATUS_FAILURE);

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState(\MultiDependencyTest::class . '::testFive'));

        $cache->load();

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState(\MultiDependencyTest::class . '::someTest'));
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $cache->getState(\MultiDependencyTest::class . '::testFive'));
    }

    public function testShouldNotSerializePassedTestsAsDefectButTimeIsStored(): void
    {
        $expectedSerializedData = PHP_VERSION_ID >= 70400
            ? 'O:37:"PHPUnit\Runner\DefaultTestResultCache":2:{s:7:"defects";a:0:{}s:5:"times";a:1:{s:7:"testOne";d:123;}}'
            : 'C:37:"PHPUnit\Runner\DefaultTestResultCache":64:{a:2:{s:7:"defects";a:0:{}s:5:"times";a:1:{s:7:"testOne";d:123;}}}';

        $cache = new DefaultTestResultCache;
        $cache->setState('testOne', BaseTestRunner::STATUS_PASSED);
        $cache->setTime('testOne', 123);

        $data = \serialize($cache);
        $this->assertSame($expectedSerializedData, $data);
    }

    public function testCanPersistCacheToFile(): void
    {
        // Create a cache with one result and store it
        $cacheFile = \tempnam(\sys_get_temp_dir(), 'phpunit_');
        $cache     = new DefaultTestResultCache($cacheFile);
        $testName  = 'test' . \uniqid();
        $cache->setState($testName, BaseTestRunner::STATUS_SKIPPED);
        $cache->persist();
        unset($cache);

        // Load the cache we just created
        $loadedCache = new DefaultTestResultCache($cacheFile);
        $loadedCache->load();
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $loadedCache->getState($testName));

        // Clean up
        \unlink($cacheFile);
    }

    public function testShouldReturnEmptyCacheWhenFileDoesNotExist(): void
    {
        $cache = new DefaultTestResultCache('/a/wrong/path/file');
        $cache->load();

        $this->assertTrue($this->isSerializedEmptyCache(\serialize($cache)));
    }

    public function testShouldReturnEmptyCacheFromInvalidFile(): void
    {
        $cacheFile = \tempnam(\sys_get_temp_dir(), 'phpunit_');
        \file_put_contents($cacheFile, '<certainly not serialized php>');

        $cache = new DefaultTestResultCache($cacheFile);
        $cache->load();

        $this->assertTrue($this->isSerializedEmptyCache(\serialize($cache)));
    }

    public function testUnserializeFromLegacyFormat(): void
    {
        $cache = \unserialize('C:37:"PHPUnit\Runner\DefaultTestResultCache":44:{a:2:{s:7:"defects";a:0:{}s:5:"times";a:0:{}}}');

        $this->assertInstanceOf(DefaultTestResultCache::class, $cache);
        $this->assertTrue($this->isSerializedEmptyCache(\serialize($cache)));
    }

    public function isSerializedEmptyCache(string $data): bool
    {
        return PHP_VERSION_ID >= 70400
            ? $data === 'O:37:"PHPUnit\Runner\DefaultTestResultCache":2:{s:7:"defects";a:0:{}s:5:"times";a:0:{}}'
            : $data === 'C:37:"PHPUnit\Runner\DefaultTestResultCache":44:{a:2:{s:7:"defects";a:0:{}s:5:"times";a:0:{}}}';
    }
}
