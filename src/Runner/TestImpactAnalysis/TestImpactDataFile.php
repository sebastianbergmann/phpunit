<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use const DIRECTORY_SEPARATOR;
use const LOCK_EX;
use const PHP_VERSION_ID;
use function array_key_exists;
use function array_search;
use function assert;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_file;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;
use function sort;
use PHPUnit\Runner\DirectoryDoesNotExistException;
use PHPUnit\Runner\Exception;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Filesystem;

/**
 * The file the source files that were executed by each test are kept in.
 *
 * Each test refers to the version of a source file it executed, and a version
 * is a source file together with the hash of its contents at the time the test
 * executed it. That is what makes the data usable later: a test whose files
 * all still hash the same executed the code that is there now, and a test that
 * refers to a version that no longer exists executed code that has changed
 * since.
 *
 * The file also records where what is in it comes from: what a test executed
 * and what a test declares that it covers and uses are different claims, and
 * the file is discarded rather than added to when it was written from the
 * other one.
 *
 * The file records what everything in it rests on as well: the version of
 * PHPUnit and the version of PHP, which decide what the data means, and the
 * assumptions about the configuration, the code that is first-party code, and
 * the packages that are installed. The file is discarded, and not merged with,
 * when any of them does not match.
 *
 * The file is written for the machine it was written on and cannot be shared
 * with another machine: the source files are named by their absolute path.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-type VersionType array{0: int, 1: non-empty-string}
 */
final class TestImpactDataFile
{
    private const int VERSION             = 3;
    private const string DEFAULT_FILENAME = 'test-impact-data';
    private readonly string $filename;
    private readonly Assumptions $assumptions;
    private readonly PathHasher $hasher;

    public function __construct(string $filepath, Assumptions $assumptions, ?PathHasher $hasher = null)
    {
        $this->assumptions = $assumptions;

        if (is_dir($filepath)) {
            $filepath .= DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME;
        }

        $this->filename = $filepath;

        if ($hasher === null) {
            $hasher = new PathHasher;
        }

        $this->hasher = $hasher;
    }

    /**
     * The tests that are recorded as having executed the source file.
     *
     * A file that cannot be hashed, because it is no longer there, is treated
     * as a file that changed: every test that executed it executed a version
     * of it that no longer exists.
     *
     * @param non-empty-string $file
     */
    public function testsThatDependOn(string $file): RecordedTests
    {
        [$files, $versions, $tests, $provenance] = $this->read();

        if ($provenance === null) {
            $provenance = Provenance::ObservedExecution;
        }

        $position = array_search($file, $files, true);

        if ($position === false) {
            return RecordedTests::from([], [], $provenance);
        }

        $hash                                  = $this->hasher->hash($file);
        $thatDependOnTheFileAsItIsNow          = [];
        $thatDependOnAnEarlierVersionOfTheFile = [];

        foreach ($tests as $test => $versionsOfTest) {
            foreach ($versionsOfTest as $versionPosition) {
                assert(isset($versions[$versionPosition]));

                if ($versions[$versionPosition][0] !== $position) {
                    continue;
                }

                if ($hash !== null && $versions[$versionPosition][1] === $hash) {
                    $thatDependOnTheFileAsItIsNow[] = $test;
                } else {
                    $thatDependOnAnEarlierVersionOfTheFile[] = $test;
                }

                break;
            }
        }

        sort($thatDependOnTheFileAsItIsNow);
        sort($thatDependOnAnEarlierVersionOfTheFile);

        return RecordedTests::from($thatDependOnTheFileAsItIsNow, $thatDependOnAnEarlierVersionOfTheFile, $provenance);
    }

    /**
     * What an earlier test run recorded for a test that was not run again is
     * written back unchanged: a run that did not run a test did not learn
     * anything about it and must not cause what is known about it to be
     * forgotten.
     *
     * @throws Exception
     */
    public function persist(TestImpactData $data, Provenance $provenance): void
    {
        [$files, $versions, $tests, $provenanceOfWhatIsThere] = $this->read();

        if ($provenanceOfWhatIsThere !== null && $provenanceOfWhatIsThere !== $provenance) {
            $files    = [];
            $versions = [];
            $tests    = [];
        }

        $filePositions = [];

        foreach ($files as $position => $file) {
            $filePositions[$file] = $position;
        }

        $versionPositions = [];

        foreach ($versions as $position => $version) {
            $versionPositions[$version[0] . ':' . $version[1]] = $position;
        }

        foreach ($data->recorded() as $test => $recordedFiles) {
            $versionsOfTest = [];
            $complete       = true;

            foreach ($recordedFiles as $file) {
                $hash = $this->hasher->hash($file);

                /*
                 * A file that cannot be hashed cannot be compared to what it
                 * will be on a later run. Recording the test without the file
                 * would make the test look as if it did not depend on it,
                 * whereas not recording the test at all leaves a test nothing
                 * is known about, and a test nothing is known about is a test
                 * that has to be run.
                 */
                if ($hash === null) {
                    $complete = false;

                    break;
                }

                if (!isset($filePositions[$file])) {
                    $filePositions[$file] = count($files);
                    $files[]              = $file;
                }

                $key = $filePositions[$file] . ':' . $hash;

                if (!isset($versionPositions[$key])) {
                    $versionPositions[$key] = count($versions);
                    $versions[]             = [$filePositions[$file], $hash];
                }

                $versionsOfTest[] = $versionPositions[$key];
            }

            if (!$complete) {
                unset($tests[$test]);

                continue;
            }

            $tests[$test] = $versionsOfTest;
        }

        $this->write($files, $versions, $tests, $provenance);
    }

    /**
     * Versions and files that no test refers to any longer are dropped, and
     * what is left is numbered again: a source file that is recorded with a
     * new hash on every run would otherwise make the file grow without bound.
     *
     * @param list<non-empty-string>             $files
     * @param list<VersionType>                  $versions
     * @param array<non-empty-string, list<int>> $tests
     *
     * @throws Exception
     */
    private function write(array $files, array $versions, array $tests, Provenance $provenance): void
    {
        if (!Filesystem::createDirectory(dirname($this->filename))) {
            throw new DirectoryDoesNotExistException(dirname($this->filename));
        }

        $keptFiles            = [];
        $keptFilePositions    = [];
        $keptVersions         = [];
        $keptVersionPositions = [];
        $keptTests            = [];

        foreach ($tests as $test => $versionsOfTest) {
            $keptVersionsOfTest = [];

            foreach ($versionsOfTest as $versionPosition) {
                if (!isset($keptVersionPositions[$versionPosition])) {
                    assert(isset($versions[$versionPosition]));

                    $version = $versions[$versionPosition];

                    assert(isset($files[$version[0]]));

                    $file = $files[$version[0]];

                    if (!isset($keptFilePositions[$file])) {
                        $keptFilePositions[$file] = count($keptFiles);
                        $keptFiles[]              = $file;
                    }

                    $keptVersionPositions[$versionPosition] = count($keptVersions);
                    $keptVersions[]                         = [$keptFilePositions[$file], $version[1]];
                }

                $keptVersionsOfTest[] = $keptVersionPositions[$versionPosition];
            }

            $keptTests[$test] = $keptVersionsOfTest;
        }

        $json = json_encode(
            [
                'version'     => self::VERSION,
                'phpunit'     => Version::id(),
                'php'         => PHP_VERSION_ID,
                'provenance'  => $provenance->value,
                'assumptions' => $this->assumptions->asArray(),
                'files'       => $keptFiles,
                'versions'    => $keptVersions,
                'tests'       => $keptTests,
            ],
        );

        /*
         * The name of a source file or of a test that is not valid UTF-8
         * cannot be written as JSON. The data that is already there is kept in
         * that case: it is what an earlier test run recorded, and every source
         * file it names is checked against the file that is there now before
         * it is used.
         */
        if ($json === false) {
            return; // @codeCoverageIgnore
        }

        file_put_contents($this->filename, $json, LOCK_EX);
    }

    /**
     * Returns empty data when there is nothing to read, when what is there
     * cannot be read, or when it was written by a different version of PHPUnit
     * or of PHP.
     *
     * @return array{0: list<non-empty-string>, 1: list<VersionType>, 2: array<non-empty-string, list<int>>, 3: ?Provenance}
     */
    private function read(): array
    {
        $empty = [[], [], [], null];

        if (!is_file($this->filename)) {
            return $empty;
        }

        $contents = file_get_contents($this->filename);

        if ($contents === false) {
            return $empty; // @codeCoverageIgnore
        }

        $data = json_decode($contents, true);

        if (!is_array($data)) {
            return $empty;
        }

        if (!isset($data['version'], $data['phpunit'], $data['php'], $data['provenance'], $data['assumptions'], $data['files'], $data['versions'], $data['tests'])) {
            return $empty;
        }

        $assumptions = Assumptions::fromArray($data['assumptions']);

        /*
         * What was recorded under other assumptions describes a state of
         * affairs that no longer exists, and is discarded rather than added to.
         */
        if ($assumptions === null || !$assumptions->equals($this->assumptions)) {
            return $empty;
        }

        if (!is_string($data['provenance'])) {
            return $empty;
        }

        $provenance = Provenance::tryFrom($data['provenance']);

        if ($provenance === null) {
            return $empty;
        }

        if ($data['version'] !== self::VERSION || $data['phpunit'] !== Version::id() || $data['php'] !== PHP_VERSION_ID) {
            return $empty;
        }

        if (!is_array($data['files']) || !is_array($data['versions']) || !is_array($data['tests'])) {
            return $empty;
        }

        $files = [];

        foreach ($data['files'] as $position => $file) {
            if ($position !== count($files) || !is_string($file) || $file === '') {
                return $empty;
            }

            $files[] = $file;
        }

        $versions = [];

        foreach ($data['versions'] as $position => $version) {
            if ($position !== count($versions) || !is_array($version) || !array_key_exists(0, $version) || !array_key_exists(1, $version)) {
                return $empty;
            }

            if (!is_int($version[0]) || !isset($files[$version[0]]) || !is_string($version[1]) || $version[1] === '') {
                return $empty;
            }

            $versions[] = [$version[0], $version[1]];
        }

        $tests = [];

        foreach ($data['tests'] as $test => $versionsOfTest) {
            if (!is_string($test) || $test === '' || !is_array($versionsOfTest)) {
                return $empty;
            }

            $versionsOfSingleTest = [];

            foreach ($versionsOfTest as $versionPosition) {
                if (!is_int($versionPosition) || !isset($versions[$versionPosition])) {
                    return $empty;
                }

                $versionsOfSingleTest[] = $versionPosition;
            }

            $tests[$test] = $versionsOfSingleTest;
        }

        return [$files, $versions, $tests, $provenance];
    }
}
