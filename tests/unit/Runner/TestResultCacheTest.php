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
use PHPUnit\Framework\TestStatus\TestStatus;
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

        $this->assertTrue($cache->status(\MultiDependencyTest::class . '::testOne')->isUnknown());
        $this->assertTrue($cache->status(\MultiDependencyTest::class . '::testFive')->isSkipped());
    }

    public function testDoesClearCacheBeforeLoad(): void
    {
        $cacheFile = TEST_FILES_PATH . '../end-to-end/execution-order/_files/MultiDependencyTest_result_cache.txt';
        $cache     = new DefaultTestResultCache($cacheFile);
        $cache->setStatus('someTest', TestStatus::failure());

        $this->assertTrue($cache->status(\MultiDependencyTest::class . '::testFive')->isUnknown());

        $cache->load();

        $this->assertTrue($cache->status(\MultiDependencyTest::class . '::someTest')->isUnknown());
        $this->assertTrue($cache->status(\MultiDependencyTest::class . '::testFive')->isSkipped());
    }

    public function testShouldNotSerializePassedTestsAsDefectButTimeIsStored(): void
    {
        $cache = new DefaultTestResultCache;
        $cache->setStatus('testOne', TestStatus::success());
        $cache->setTime('testOne', 123);

        $data = \serialize($cache);
        $this->assertSame('C:37:"PHPUnit\Runner\DefaultTestResultCache":64:{a:2:{s:7:"defects";a:0:{}s:5:"times";a:1:{s:7:"testOne";d:123;}}}', $data);
    }

    public function testCanPersistCacheToFile(): void
    {
        // Create a cache with one result and store it
        $cacheFile = \tempnam(\sys_get_temp_dir(), 'phpunit_');
        $cache     = new DefaultTestResultCache($cacheFile);
        $testName  = 'test' . \uniqid();
        $cache->setStatus($testName, TestStatus::skipped());
        $cache->persist();
        unset($cache);

        // Load the cache we just created
        $loadedCache = new DefaultTestResultCache($cacheFile);
        $loadedCache->load();
        $this->assertTrue($loadedCache->status($testName)->isSkipped());

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

        \unlink($cacheFile);
    }

    public function isSerializedEmptyCache(string $data): bool
    {
        return $data === 'C:37:"PHPUnit\Runner\DefaultTestResultCache":44:{a:2:{s:7:"defects";a:0:{}s:5:"times";a:0:{}}}';
    }
}
