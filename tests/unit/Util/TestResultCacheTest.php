<?php
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
use PHPUnit\Runner\TestResultCache;

/**
 * @group test-reorder
 */
class TestResultCacheTest extends TestCase
{
    public function testReadsCacheFromProvidedFilename(): void
    {
        $cacheFile = TEST_FILES_PATH . '/MultiDependencyTest_result_cache.txt';
        $cache     = new TestResultCache($cacheFile);
        $cache->load();

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState('testOne'));
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $cache->getState('testFive'));
    }

    public function testDoesClearCacheBeforeLoad(): void
    {
        $cacheFile = TEST_FILES_PATH . '/MultiDependencyTest_result_cache.txt';
        $cache     = new TestResultCache($cacheFile);
        $cache->setState('someTest', BaseTestRunner::STATUS_FAILURE);

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState('testFive'));

        $cache->load();

        $this->assertSame(BaseTestRunner::STATUS_UNKNOWN, $cache->getState('someTest'));
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $cache->getState('testFive'));
    }

    public function testShouldNotSerializePassedTestsAsDefectButTimeIsStored(): void
    {
        $cache = new TestResultCache;
        $cache->setState('testOne', BaseTestRunner::STATUS_PASSED);
        $cache->setTime('testOne', 123);

        $data = \serialize($cache);
        $this->assertSame('C:30:"PHPUnit\Runner\TestResultCache":64:{a:2:{s:7:"defects";a:0:{}s:5:"times";a:1:{s:7:"testOne";d:123;}}}', $data);
    }

    public function testCanPersistCacheToFile(): void
    {
        // Create a cache with one result and store it
        $cacheFile = \tempnam(\sys_get_temp_dir(), 'phpunit_');
        $cache     = new TestResultCache($cacheFile);
        $testName  = 'test' . \uniqid();
        $cache->setState($testName, BaseTestRunner::STATUS_SKIPPED);
        $cache->persist();
        unset($cache);

        // Load the cache we just created
        $loadedCache = new TestResultCache($cacheFile);
        $loadedCache->load();
        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $loadedCache->getState($testName));

        // Clean up
        \unlink($cacheFile);
    }

    public function testShouldReturnEmptyCacheWhenFileDoesNotExist(): void
    {
        $cache = new TestResultCache('/a/wrong/path/file');
        $cache->load();

        $this->assertTrue($this->isSerializedEmptyCache(\serialize($cache)));
    }

    public function testShouldReturnEmptyCacheFromInvalidFile(): void
    {
        $cacheFile = \tempnam(\sys_get_temp_dir(), 'phpunit_');
        \file_put_contents($cacheFile, '<certainly not serialized php>');

        $cache = new TestResultCache($cacheFile);
        $cache->load();

        $this->assertTrue($this->isSerializedEmptyCache(\serialize($cache)));
    }

    public function isSerializedEmptyCache(string $data): bool
    {
        return $data === 'C:30:"PHPUnit\Runner\TestResultCache":44:{a:2:{s:7:"defects";a:0:{}s:5:"times";a:0:{}}}';
    }
}
