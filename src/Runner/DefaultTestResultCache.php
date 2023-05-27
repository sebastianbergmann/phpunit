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

use const DIRECTORY_SEPARATOR;
use const LOCK_EX;
use function assert;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function json_decode;
use function json_encode;
use function sprintf;
use PHPUnit\Util\Filesystem;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DefaultTestResultCache implements TestResultCache
{
    /**
     * @var int
     */
    private const VERSION = 1;

    /**
     * @psalm-var list<int>
     */
    private const ALLOWED_TEST_STATUSES = [
        BaseTestRunner::STATUS_SKIPPED,
        BaseTestRunner::STATUS_INCOMPLETE,
        BaseTestRunner::STATUS_FAILURE,
        BaseTestRunner::STATUS_ERROR,
        BaseTestRunner::STATUS_RISKY,
        BaseTestRunner::STATUS_WARNING,
    ];

    /**
     * @var string
     */
    private const DEFAULT_RESULT_CACHE_FILENAME = '.phpunit.result.cache';

    /**
     * @var string
     */
    private $cacheFilename;

    /**
     * @psalm-var array<string, int>
     */
    private $defects = [];

    /**
     * @psalm-var array<string, float>
     */
    private $times = [];

    public function __construct(?string $filepath = null)
    {
        if ($filepath !== null && is_dir($filepath)) {
            $filepath .= DIRECTORY_SEPARATOR . self::DEFAULT_RESULT_CACHE_FILENAME;
        }

        $this->cacheFilename = $filepath ?? $_ENV['PHPUNIT_RESULT_CACHE'] ?? self::DEFAULT_RESULT_CACHE_FILENAME;
    }

    public function setState(string $testName, int $state): void
    {
        if (!in_array($state, self::ALLOWED_TEST_STATUSES, true)) {
            return;
        }

        $this->defects[$testName] = $state;
    }

    public function getState(string $testName): int
    {
        return $this->defects[$testName] ?? BaseTestRunner::STATUS_UNKNOWN;
    }

    public function setTime(string $testName, float $time): void
    {
        $this->times[$testName] = $time;
    }

    public function getTime(string $testName): float
    {
        return $this->times[$testName] ?? 0.0;
    }

    public function load(): void
    {
        if (!is_file($this->cacheFilename)) {
            return;
        }

        $data = json_decode(
            file_get_contents($this->cacheFilename),
            true,
        );

        if ($data === null) {
            return;
        }

        if (!isset($data['version'])) {
            return;
        }

        if ($data['version'] !== self::VERSION) {
            return;
        }

        assert(isset($data['defects']) && is_array($data['defects']));
        assert(isset($data['times']) && is_array($data['times']));

        $this->defects = $data['defects'];
        $this->times   = $data['times'];
    }

    /**
     * @throws Exception
     */
    public function persist(): void
    {
        if (!Filesystem::createDirectory(dirname($this->cacheFilename))) {
            throw new Exception(
                sprintf(
                    'Cannot create directory "%s" for result cache file',
                    $this->cacheFilename,
                ),
            );
        }

        file_put_contents(
            $this->cacheFilename,
            json_encode(
                [
                    'version' => self::VERSION,
                    'defects' => $this->defects,
                    'times'   => $this->times,
                ],
            ),
            LOCK_EX,
        );
    }
}
