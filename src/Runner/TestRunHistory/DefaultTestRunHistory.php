<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestRunHistory;

use const DIRECTORY_SEPARATOR;
use const LOCK_EX;
use const LOCK_UN;
use function array_keys;
use function dirname;
use function fclose;
use function file_get_contents;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function is_array;
use function is_dir;
use function is_file;
use function is_float;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function rewind;
use function stream_get_contents;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\DirectoryDoesNotExistException;
use PHPUnit\Runner\Exception;
use PHPUnit\Util\Filesystem;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DefaultTestRunHistory implements TestRunHistory
{
    private const int VERSION = 2;

    /**
     * This filename is only used when no cache directory is configured. It is
     * intentionally not renamed along with the feature: it is commonly listed
     * in .gitignore files and renaming it would leave an untracked file behind.
     */
    private const string DEFAULT_FILENAME = '.phpunit.result.cache';
    private readonly string $filename;

    /**
     * @var array<string, TestStatus>
     */
    private array $defects = [];

    /**
     * @var array<string, float>
     */
    private array $times = [];

    /**
     * @var array<string, true>
     */
    private array $changedDefects = [];

    /**
     * @var array<string, true>
     */
    private array $changedTimes = [];

    public function __construct(string $filepath)
    {
        if (is_dir($filepath)) {
            $filepath .= DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME;
        }

        $this->filename = $filepath;
    }

    public function setStatus(TestRunHistoryId $id, TestStatus $status): void
    {
        if ($status->isSuccess()) {
            return;
        }

        $this->defects[$id->asString()]        = $status;
        $this->changedDefects[$id->asString()] = true;
    }

    public function remove(TestRunHistoryId $id): void
    {
        unset($this->defects[$id->asString()]);

        $this->changedDefects[$id->asString()] = true;
    }

    public function status(TestRunHistoryId $id): TestStatus
    {
        return $this->defects[$id->asString()] ?? TestStatus::unknown();
    }

    public function setTime(TestRunHistoryId $id, float $time): void
    {
        $this->times[$id->asString()]        = $time;
        $this->changedTimes[$id->asString()] = true;
    }

    public function time(TestRunHistoryId $id): float
    {
        return $this->times[$id->asString()] ?? 0.0;
    }

    public function mergeWith(self $other): void
    {
        foreach ($other->defects as $id => $defect) {
            $this->defects[$id]        = $defect;
            $this->changedDefects[$id] = true;
        }

        foreach ($other->times as $id => $time) {
            $this->times[$id]        = $time;
            $this->changedTimes[$id] = true;
        }
    }

    public function load(): void
    {
        if (!is_file($this->filename)) {
            return;
        }

        $contents = file_get_contents($this->filename);

        if ($contents === false) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $parsed = $this->parse($contents);

        if ($parsed === null) {
            return;
        }

        [$this->defects, $this->times] = $parsed;

        $this->changedDefects = [];
        $this->changedTimes   = [];
    }

    /**
     * @throws Exception
     */
    public function persist(): void
    {
        $this->writeToFile(false);
    }

    /**
     * Persists only the entries that were touched since load(). This drops
     * the entries of tests that no longer exist and must only be used when
     * the current test run executed every test that exists.
     *
     * @throws Exception
     */
    public function persistAndPrune(): void
    {
        $this->writeToFile(true);
    }

    /**
     * @throws Exception
     */
    private function writeToFile(bool $prune): void
    {
        if (!Filesystem::createDirectory(dirname($this->filename))) {
            throw new DirectoryDoesNotExistException(dirname($this->filename));
        }

        $handle = fopen($this->filename, 'c+');

        if ($handle === false) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        flock($handle, LOCK_EX);

        if ($prune) {
            $defects = [];

            foreach ($this->defects as $id => $status) {
                if (isset($this->changedDefects[$id])) {
                    $defects[$id] = $status;
                }
            }

            $times = [];

            // A test whose status was recorded but whose time was not is a
            // test that was skipped in this run; its previously recorded time
            // must survive pruning
            foreach ($this->times as $id => $time) {
                if (isset($this->changedTimes[$id]) || isset($this->changedDefects[$id])) {
                    $times[$id] = $time;
                }
            }
        } else {
            // Another test run may have persisted its results between this
            // run's load() and now; only overlaying this run's changes onto
            // the current file contents keeps that run's results for tests
            // this run did not execute
            $parsed = $this->parse((string) stream_get_contents($handle));

            if ($parsed !== null) {
                [$defects, $times] = $parsed;

                foreach (array_keys($this->changedDefects) as $id) {
                    if (isset($this->defects[$id])) {
                        $defects[$id] = $this->defects[$id];
                    } else {
                        unset($defects[$id]);
                    }
                }

                foreach ($this->times as $id => $time) {
                    if (isset($this->changedTimes[$id])) {
                        $times[$id] = $time;
                    }
                }
            } else {
                $defects = $this->defects;
                $times   = $this->times;
            }
        }

        $data = [
            'version' => self::VERSION,
            'defects' => [],
            'times'   => $times,
        ];

        foreach ($defects as $test => $status) {
            $data['defects'][$test] = $status->asInt();
        }

        $json = json_encode($data);

        if ($json !== false) {
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $json);
        }

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * @return ?array{0: array<string, TestStatus>, 1: array<string, float>}
     */
    private function parse(string $contents): ?array
    {
        $data = json_decode(
            $contents,
            true,
        );

        if (!is_array($data)) {
            return null;
        }

        if (!isset($data['version']) || $data['version'] !== self::VERSION) {
            return null;
        }

        if (!isset($data['defects'], $data['times']) || !is_array($data['defects']) || !is_array($data['times'])) {
            return null;
        }

        $defects = [];

        foreach ($data['defects'] as $test => $status) {
            if (!is_string($test) || !is_int($status)) {
                continue;
            }

            $defects[$test] = TestStatus::from($status);
        }

        $times = [];

        foreach ($data['times'] as $test => $time) {
            if (!is_string($test) || (!is_float($time) && !is_int($time))) {
                continue;
            }

            $times[$test] = (float) $time;
        }

        return [$defects, $times];
    }
}
