<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\Test;

class TestResultCache implements \Serializable, TestResultCacheInterface
{
    /**
     * @var string
     */
    public const DEFAULT_RESULT_CACHE_FILENAME = '.phpunit.result.cache';

    /**
     * Provide extra protection against incomplete or corrupt caches
     *
     * @var array<string, string>
     */
    private const ALLOWED_CACHE_TEST_STATUSES = [
        BaseTestRunner::STATUS_SKIPPED,
        BaseTestRunner::STATUS_INCOMPLETE,
        BaseTestRunner::STATUS_FAILURE,
        BaseTestRunner::STATUS_ERROR,
        BaseTestRunner::STATUS_RISKY,
        BaseTestRunner::STATUS_WARNING,
    ];

    /**
     * Path and filename for result cache file
     *
     * @var string
     */
    private $cacheFilename;

    /**
     * The list of defective tests
     *
     * <code>
     * // Mark a test skipped
     * $this->defects[$testName] = BaseTestRunner::TEST_SKIPPED;
     * </code>
     *
     * @var array array<string, int>
     */
    private $defects = [];

    /**
     * The list of execution duration of suites and tests (in seconds)
     *
     * <code>
     * // Record running time for test
     * $this->times[$testName] = 1.234;
     * </code>
     *
     * @var array<string, float>
     */
    private $times = [];

    public function __construct($filename = null)
    {
        $this->cacheFilename = $filename ?? $_ENV['PHPUNIT_RESULT_CACHE'] ?? self::DEFAULT_RESULT_CACHE_FILENAME;
    }

    public function persist(): void
    {
        $this->saveToFile();
    }

    public function saveToFile(): void
    {
        if (\defined('PHPUNIT_TESTSUITE_RESULTCACHE')) {
            return;
        }

        if (!$this->createDirectory(\dirname($this->cacheFilename))) {
            throw new Exception(
                \sprintf(
                    'Cannot create directory "%s" for result cache file',
                    $this->cacheFilename
                )
            );
        }

        \file_put_contents(
            $this->cacheFilename,
            \serialize($this)
        );
    }

    public function setState(string $testName, int $state): void
    {
        if ($state !== BaseTestRunner::STATUS_PASSED) {
            $this->defects[$testName] = $state;
        }
    }

    public function getState($testName): int
    {
        return $this->defects[$testName] ?? BaseTestRunner::STATUS_UNKNOWN;
    }

    public function setTime(string $testName, float $time): void
    {
        $this->times[$testName] = $time;
    }

    public function getTime($testName): float
    {
        return $this->times[$testName] ?? 0;
    }

    public function load(): void
    {
        $this->clear();

        if (\is_file($this->cacheFilename) === false) {
            return;
        }

        $cacheData = @\file_get_contents($this->cacheFilename);

        // @codeCoverageIgnoreStart
        if ($cacheData === false) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $cache = @\unserialize($cacheData, ['allowed_classes' => [self::class]]);

        if ($cache === false) {
            return;
        }

        if ($cache instanceof self) {
            /* @var \PHPUnit\Runner\TestResultCache */
            $cache->copyStateToCache($this);
        }
    }

    public function copyStateToCache(self $targetCache): void
    {
        foreach ($this->defects as $name => $state) {
            $targetCache->setState($name, $state);
        }

        foreach ($this->times as $name => $time) {
            $targetCache->setTime($name, $time);
        }
    }

    public function clear(): void
    {
        $this->defects = [];
        $this->times   = [];
    }

    public function serialize(): string
    {
        return \serialize([
            'defects' => $this->defects,
            'times'   => $this->times,
        ]);
    }

    public function unserialize($serialized): void
    {
        $data = \unserialize($serialized);

        if (isset($data['times'])) {
            foreach ($data['times'] as $testName => $testTime) {
                $this->times[$testName] = (float) $testTime;
            }
        }

        if (isset($data['defects'])) {
            foreach ($data['defects'] as $testName => $testResult) {
                if (\in_array($testResult, self::ALLOWED_CACHE_TEST_STATUSES, true)) {
                    $this->defects[$testName] = $testResult;
                }
            }
        }
    }

    private function createDirectory(string $directory): bool
    {
        return !(!\is_dir($directory) && !@\mkdir($directory, 0777, true) && !\is_dir($directory));
    }
}
