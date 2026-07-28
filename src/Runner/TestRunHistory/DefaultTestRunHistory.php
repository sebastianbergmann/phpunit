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
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_file;
use function is_float;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
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

        $this->defects[$id->asString()] = $status;
    }

    public function remove(TestRunHistoryId $id): void
    {
        unset($this->defects[$id->asString()]);
    }

    public function status(TestRunHistoryId $id): TestStatus
    {
        return $this->defects[$id->asString()] ?? TestStatus::unknown();
    }

    public function setTime(TestRunHistoryId $id, float $time): void
    {
        $this->times[$id->asString()] = $time;
    }

    public function time(TestRunHistoryId $id): float
    {
        return $this->times[$id->asString()] ?? 0.0;
    }

    public function mergeWith(self $other): void
    {
        foreach ($other->defects as $id => $defect) {
            $this->defects[$id] = $defect;
        }

        foreach ($other->times as $id => $time) {
            $this->times[$id] = $time;
        }
    }

    public function load(): void
    {
        if (!is_file($this->filename)) {
            return;
        }

        $contents = file_get_contents($this->filename);

        if ($contents === false) {
            return;
        }

        $data = json_decode(
            $contents,
            true,
        );

        if (!is_array($data)) {
            return;
        }

        if (!isset($data['version']) || $data['version'] !== self::VERSION) {
            return;
        }

        if (!isset($data['defects'], $data['times']) || !is_array($data['defects']) || !is_array($data['times'])) {
            return;
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

        $this->defects = $defects;
        $this->times   = $times;
    }

    /**
     * @throws Exception
     */
    public function persist(): void
    {
        if (!Filesystem::createDirectory(dirname($this->filename))) {
            throw new DirectoryDoesNotExistException(dirname($this->filename));
        }

        $data = [
            'version' => self::VERSION,
            'defects' => [],
            'times'   => $this->times,
        ];

        foreach ($this->defects as $test => $status) {
            $data['defects'][$test] = $status->asInt();
        }

        file_put_contents(
            $this->filename,
            json_encode($data),
            LOCK_EX,
        );
    }
}
