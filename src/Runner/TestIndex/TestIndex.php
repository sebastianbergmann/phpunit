<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use const DIRECTORY_SEPARATOR;
use const LOCK_EX;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_bool;
use function is_dir;
use function is_file;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function realpath;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\DirectoryDoesNotExistException;
use PHPUnit\Runner\Exception;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Filesystem;
use ReflectionClass;

/**
 * What is known about the tests in a set of test files without loading them.
 *
 * The index makes it possible to decide that a test file cannot contribute a
 * test to a run before the file is loaded. An entry is only handed out while
 * every source file it was derived from is unchanged, and the index as a whole
 * is discarded when it was written by a different version of PHPUnit, as the
 * meaning of the metadata it stores is defined by that version.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestIndex
{
    private const int VERSION                   = 3;
    private const string DEFAULT_INDEX_FILENAME = 'test-index';
    private readonly string $indexFilename;
    private readonly FileHasher $hasher;

    /**
     * @var array<non-empty-string, TestIndexEntry>
     */
    private array $entries = [];

    public function __construct(string $filepath, ?FileHasher $hasher = null)
    {
        if (is_dir($filepath)) {
            $filepath .= DIRECTORY_SEPARATOR . self::DEFAULT_INDEX_FILENAME;
        }

        $this->indexFilename = $filepath;

        if ($hasher === null) {
            $hasher = new FileHasher;
        }

        $this->hasher = $hasher;
    }

    /**
     * Returns null when the file is not indexed or when the entry for it is no
     * longer valid. Callers must load the file in that case.
     */
    public function entryFor(string $file): ?TestIndexEntry
    {
        $file = realpath($file);

        if ($file === false) {
            return null;
        }

        if (!isset($this->entries[$file])) {
            return null;
        }

        $entry = $this->entries[$file];

        if (!$entry->isValid($this->hasher)) {
            unset($this->entries[$file]);

            return null;
        }

        return $entry;
    }

    /**
     * @param ReflectionClass<TestCase> $class
     */
    public function record(ReflectionClass $class, bool $madePhpUnitWarn): void
    {
        $file = $class->getFileName();

        if ($file === false || $file === '') {
            return;
        }

        $entry = TestIndexEntry::for($class, $this->hasher, $madePhpUnitWarn);

        if ($entry === null) {
            return;
        }

        $this->entries[$file] = $entry;
    }

    public function load(): void
    {
        if (!is_file($this->indexFilename)) {
            return;
        }

        $contents = file_get_contents($this->indexFilename);

        if ($contents === false) {
            return;
        }

        $data = json_decode($contents, true);

        if (!is_array($data)) {
            return;
        }

        if (!isset($data['version'], $data['phpunit'], $data['groups'], $data['entries'])) {
            return;
        }

        if ($data['version'] !== self::VERSION || $data['phpunit'] !== Version::id()) {
            return;
        }

        if (!is_array($data['groups']) || !is_array($data['entries'])) {
            return;
        }

        $groupNames = [];

        /*
         * A group name that cannot be read is left out instead of discarding
         * the index: the entries that do not use it are unaffected, and the
         * ones that do are dropped when they are read. Leaving it out must not
         * move the other names, which are referred to by their position.
         */
        foreach ($data['groups'] as $position => $groupName) {
            if (!is_int($position) || !is_string($groupName) || $groupName === '') {
                continue;
            }

            $groupNames[$position] = $groupName;
        }

        foreach ($data['entries'] as $file => $entry) {
            $entry = self::entryFromArray($groupNames, $entry);

            if ($entry === null || !is_string($file) || $file === '') {
                continue;
            }

            $this->entries[$file] = $entry;
        }
    }

    /**
     * Entries that were not recorded during this run are written back
     * unchanged: a run that skipped a file did not learn anything about it and
     * must not cause what is known about it to be forgotten. Entries for files
     * that no longer exist are dropped.
     *
     * @throws Exception
     */
    public function persist(): void
    {
        if (!Filesystem::createDirectory(dirname($this->indexFilename))) {
            throw new DirectoryDoesNotExistException(dirname($this->indexFilename));
        }

        /*
         * The group names are collected in a list of their own, and not as the
         * keys of the map that assigns a position to each of them: a group name
         * such as "6546" would become an integer key, and would then be written
         * as an integer and no longer be readable as a group name.
         */
        $groupNames     = [];
        $groupPositions = [];
        $entries        = [];

        foreach ($this->entries as $file => $entry) {
            if (!is_file($file)) {
                continue;
            }

            $groups = [];

            foreach ($entry->groups() as $methodName => $groupNamesOfMethod) {
                $groups[$methodName] = [];

                foreach ($groupNamesOfMethod as $groupName) {
                    if (!isset($groupPositions[$groupName])) {
                        $groupPositions[$groupName] = count($groupNames);
                        $groupNames[]               = $groupName;
                    }

                    $groups[$methodName][] = $groupPositions[$groupName];
                }
            }

            $entries[$file] = [
                'class'        => $entry->className(),
                'groups'       => $groups,
                'dataSets'     => $entry->dataSets(),
                'warned'       => $entry->madePhpUnitWarn(),
                'dependencies' => $entry->dependencies(),
            ];
        }

        file_put_contents(
            $this->indexFilename,
            json_encode(
                [
                    'version' => self::VERSION,
                    'phpunit' => Version::id(),
                    'groups'  => $groupNames,
                    'entries' => $entries,
                ],
            ),
            LOCK_EX,
        );
    }

    /**
     * @param array<int, non-empty-string> $groupNames
     */
    private static function entryFromArray(array $groupNames, mixed $entry): ?TestIndexEntry
    {
        if (!is_array($entry) || !isset($entry['class'], $entry['groups'], $entry['dataSets'], $entry['warned'], $entry['dependencies'])) {
            return null;
        }

        if (!is_string($entry['class']) || $entry['class'] === '' || !is_array($entry['groups']) || !is_array($entry['dataSets']) || !is_bool($entry['warned']) || !is_array($entry['dependencies'])) {
            return null;
        }

        $groups = [];

        foreach ($entry['groups'] as $methodName => $groupIndexes) {
            if (!is_string($methodName) || $methodName === '' || !is_array($groupIndexes)) {
                return null;
            }

            $groups[$methodName] = [];

            foreach ($groupIndexes as $groupIndex) {
                if (!is_int($groupIndex) || !isset($groupNames[$groupIndex])) {
                    return null;
                }

                $groups[$methodName][] = $groupNames[$groupIndex];
            }
        }

        $dataSets = [];

        foreach ($entry['dataSets'] as $methodName => $hasDataSets) {
            if (!is_string($methodName) || $methodName === '' || !is_bool($hasDataSets)) {
                return null;
            }

            $dataSets[$methodName] = $hasDataSets;
        }

        $dependencies = [];

        foreach ($entry['dependencies'] as $file => $hash) {
            if (!is_string($file) || $file === '' || !is_string($hash) || $hash === '') {
                return null;
            }

            $dependencies[$file] = $hash;
        }

        if ($dependencies === []) {
            return null;
        }

        /** @var class-string<TestCase> $className */
        $className = $entry['class'];

        return TestIndexEntry::from($className, $groups, $dataSets, $entry['warned'], $dependencies);
    }
}
