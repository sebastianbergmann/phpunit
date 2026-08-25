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
use const PHP_EOL;
use const PHP_VERSION_ID;
use function basename;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sort;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\DirectoryDoesNotExistException;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(TestImpactDataFile::class)]
#[UsesClass(DefaultTestImpactData::class)]
#[UsesClass(Recording::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class TestImpactDataFileTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

    public static function provideUnusableData(): array
    {
        $usable = [
            'version'     => 4,
            'phpunit'     => Version::id(),
            'php'         => PHP_VERSION_ID,
            'provenance'  => 'observed-execution',
            'assumptions' => self::assumptionsOfTheProvider(),
            'sourceFiles' => [],
            'files'       => ['/src/Foo.php'],
            'versions'    => [[0, 'a-hash']],
            'tests'       => ['FooTest::testOne' => [0]],
        ];

        return [
            'written by another version of PHPUnit'        => [['phpunit' => 'another-version'] + $usable],
            'written by another version of PHP'            => [['php' => PHP_VERSION_ID - 1] + $usable],
            'written in another format'                    => [['version' => 0] + $usable],
            'without a provenance'                         => [self::withoutKey($usable, 'provenance')],
            'with a provenance that is not a string'       => [['provenance' => 1] + $usable],
            'with an unknown provenance'                   => [['provenance' => 'guesswork'] + $usable],
            'without assumptions'                          => [self::withoutKey($usable, 'assumptions')],
            'without source files'                         => [self::withoutKey($usable, 'sourceFiles')],
            'with source files that are not an array'      => [['sourceFiles' => 'guesswork'] + $usable],
            'with a source file that is not a pair'        => [['sourceFiles' => [[0]]] + $usable],
            'with a source file that is not known'         => [['sourceFiles' => [[1, 'a-hash']]] + $usable],
            'with a source file hash that is not a string' => [['sourceFiles' => [[0, 1]]] + $usable],
            'with assumptions that are not an array'       => [['assumptions' => 'guesswork'] + $usable],
            'with assumptions that are incomplete'         => [['assumptions' => ['source' => 'a-hash']] + $usable],
            'with an unusable assumption'                  => [['assumptions' => ['configuration' => null, 'source' => 1, 'installedPackages' => null]] + $usable],
            'with other assumptions'                       => [['assumptions' => ['configuration' => null, 'source' => 'another-hash', 'installedPackages' => null]] + $usable],
            'without files'                                => [self::withoutKey($usable, 'files')],
            'without versions'                             => [self::withoutKey($usable, 'versions')],
            'without tests'                                => [self::withoutKey($usable, 'tests')],
            'with files that are not an array'             => [['files' => 'not-an-array'] + $usable],
            'with versions that are not an array'          => [['versions' => 'not-an-array'] + $usable],
            'with tests that are not an array'             => [['tests' => 'not-an-array'] + $usable],
            'with files that are not a list'               => [['files' => ['a-key' => '/src/Foo.php']] + $usable],
            'with a file that is not a string'             => [['files' => [1]] + $usable],
            'with an empty file name'                      => [['files' => ['']] + $usable],
            'with a version that is not a pair'            => [['versions' => [[0]]] + $usable],
            'with a version of an unknown file'            => [['versions' => [[1, 'a-hash']]] + $usable],
            'with a hash that is not a string'             => [['versions' => [[0, 1]]] + $usable],
            'with an empty hash'                           => [['versions' => [[0, '']]] + $usable],
            'with an empty test name'                      => [['tests' => ['' => [0]]] + $usable],
            'with a test that is not a list'               => [['tests' => ['FooTest::testOne' => 0]] + $usable],
            'with an unknown version'                      => [['tests' => ['FooTest::testOne' => [1]]] + $usable],
            'with a version that is not an integer'        => [['tests' => ['FooTest::testOne' => ['0']]] + $usable],
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            $entries = scandir($directory);

            if ($entries !== false) {
                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    unlink($directory . DIRECTORY_SEPARATOR . $entry);
                }
            }

            rmdir($directory);
        }

        $this->directories = [];
    }

    public function testPersistsWhichSourceFilesATestExecuted(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$file]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        $persisted = $this->persistedData($directory);

        $this->assertSame(4, $persisted['version']);
        $this->assertSame(Version::id(), $persisted['phpunit']);
        $this->assertSame(PHP_VERSION_ID, $persisted['php']);
        $this->assertSame([$file], $persisted['files']);
        $this->assertSame([['FooTest::testOne', 'Foo.php']], $this->dependencies($persisted));
    }

    public function testPersistsTheFileNamesOfSourceFilesAsTheyAreOnThisMachine(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$file]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        $this->assertSame($directory . DIRECTORY_SEPARATOR . 'Foo.php', $this->persistedData($directory)['files'][0]);
    }

    public function testPersistsIntoTheFileWhenOneIsNamedInsteadOfADirectory(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$file]);

        new TestImpactDataFile($directory . DIRECTORY_SEPARATOR . 'named-file', $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        $this->assertFileExists($directory . DIRECTORY_SEPARATOR . 'named-file');
        $this->assertFileDoesNotExist($directory . DIRECTORY_SEPARATOR . 'test-impact-data');
    }

    public function testKeepsWhatWasRecordedForATestThatWasNotRunAgain(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');
        $bar       = $this->writeSourceFile($directory, 'Bar', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);
        $first->record('BarTest::testOne', [$bar]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$bar, $foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($second, Provenance::ObservedExecution, []);

        $this->assertSame(
            [
                ['BarTest::testOne', 'Bar.php'],
                ['BarTest::testOne', 'Foo.php'],
                ['FooTest::testOne', 'Foo.php'],
            ],
            $this->dependencies($this->persistedData($directory)),
        );
    }

    public function testForgetsWhatWasRecordedForATestThatWasNotRunAgainWhenPruning(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');
        $bar       = $this->writeSourceFile($directory, 'Bar', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);
        $first->record('BarTest::testOne', [$bar]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$bar]);

        new TestImpactDataFile($directory, $this->assumptions())->persistAndPrune($second, Provenance::ObservedExecution, []);

        $this->assertSame(
            [
                ['BarTest::testOne', 'Bar.php'],
            ],
            $this->dependencies($this->persistedData($directory)),
        );
    }

    public function testForgetsTheSourceFilesOnlyAForgottenTestReferredTo(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');
        $bar       = $this->writeSourceFile($directory, 'Bar', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);
        $first->record('BarTest::testOne', [$bar]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$bar]);

        new TestImpactDataFile($directory, $this->assumptions())->persistAndPrune($second, Provenance::ObservedExecution, []);

        $this->assertSame([$bar], $this->persistedData($directory)['files']);
    }

    public function testKeepsTheSourceFilesThatAreSubjectToCodeCoverageAnalysisWhenPruning(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persistAndPrune(new DefaultTestImpactData, Provenance::ObservedExecution, [$foo]);

        $persisted = $this->persistedData($directory);

        $this->assertSame([], $persisted['tests']);
        $this->assertCount(1, $persisted['sourceFiles']);
        $this->assertSame($foo, $persisted['files'][$persisted['sourceFiles'][0][0]]);
    }

    public function testKeepsWhatASourceFileWasWhenNotPruning(): void
    {
        $directory = $this->temporaryDirectory();
        $covered   = $this->writeSourceFile($directory, 'Covered', 'first');
        $untested  = $this->writeSourceFile($directory, 'Untested', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$covered]);

        new TestImpactDataFile($directory, $this->assumptions())->persistAndPrune($data, Provenance::ObservedExecution, [$covered, $untested]);

        $hashOfWhatWasRecorded = $this->hashOfSourceFile($this->persistedData($directory), $untested);

        $this->writeSourceFile($directory, 'Untested', 'second');

        /*
         * A test run that did not run every test there is did not assess the
         * change to the file no test refers to, and must not record it as if
         * it had.
         */
        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, [$covered, $untested]);

        $this->assertSame(
            $hashOfWhatWasRecorded,
            $this->hashOfSourceFile($this->persistedData($directory), $untested),
        );
    }

    public function testRecordsWhatASourceFileIsNowWhenPruning(): void
    {
        $directory = $this->temporaryDirectory();
        $covered   = $this->writeSourceFile($directory, 'Covered', 'first');
        $untested  = $this->writeSourceFile($directory, 'Untested', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$covered]);

        new TestImpactDataFile($directory, $this->assumptions())->persistAndPrune($data, Provenance::ObservedExecution, [$covered, $untested]);

        $hashOfWhatWasRecorded = $this->hashOfSourceFile($this->persistedData($directory), $untested);

        $this->writeSourceFile($directory, 'Untested', 'second');

        new TestImpactDataFile($directory, $this->assumptions())->persistAndPrune($data, Provenance::ObservedExecution, [$covered, $untested]);

        $this->assertNotSame(
            $hashOfWhatWasRecorded,
            $this->hashOfSourceFile($this->persistedData($directory), $untested),
        );
    }

    public function testRecordsTheVersionOfASourceFileThatATestExecuted(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);
        $first->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($second, Provenance::ObservedExecution, []);

        $persisted = $this->persistedData($directory);

        $this->assertCount(2, $persisted['versions']);

        $versionOfFooTest = $persisted['tests']['FooTest::testOne'][0];
        $versionOfBarTest = $persisted['tests']['BarTest::testOne'][0];

        $this->assertNotSame($versionOfFooTest, $versionOfBarTest);
        $this->assertNotSame($persisted['versions'][$versionOfFooTest][1], $persisted['versions'][$versionOfBarTest][1]);
    }

    public function testForgetsTheVersionOfASourceFileNoTestRefersToAnyLonger(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($second, Provenance::ObservedExecution, []);

        $this->assertCount(1, $this->persistedData($directory)['versions']);
        $this->assertCount(1, $this->persistedData($directory)['files']);
    }

    public function testDoesNotRecordATestThatExecutedASourceFileThatCannotBeHashed(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $second = new DefaultTestImpactData;
        $second->record('FooTest::testOne', [$foo, $directory . DIRECTORY_SEPARATOR . 'DoesNotExist.php']);

        new TestImpactDataFile($directory, $this->assumptions())->persist($second, Provenance::ObservedExecution, []);

        $this->assertSame([], $this->persistedData($directory)['tests']);
    }

    public function testDiscardsWhatCannotBeReadAsJson(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        file_put_contents($directory . DIRECTORY_SEPARATOR . 'test-impact-data', 'this is not JSON');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        $this->assertSame([['FooTest::testOne', 'Foo.php']], $this->dependencies($this->persistedData($directory)));
    }

    #[DataProvider('provideUnusableData')]
    public function testDiscardsWhatItCannotUse(array $unusable): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        file_put_contents($directory . DIRECTORY_SEPARATOR . 'test-impact-data', json_encode($unusable));

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        $this->assertSame([['FooTest::testOne', 'Foo.php']], $this->dependencies($this->persistedData($directory)));
    }

    public function testThrowsExceptionWhenTheDirectoryToPersistIntoCannotBeCreated(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $this->expectException(DirectoryDoesNotExistException::class);

        new TestImpactDataFile($file . DIRECTORY_SEPARATOR . 'test-impact-data', $this->assumptions())->persist(new DefaultTestImpactData, Provenance::ObservedExecution, []);
    }

    public function testPersistsTheSourceFilesThatAreSubjectToCodeCoverageAnalysis(): void
    {
        $directory = $this->temporaryDirectory();
        $covered   = $this->writeSourceFile($directory, 'Covered', 'first');
        $untested  = $this->writeSourceFile($directory, 'Untested', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$covered]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, [$covered, $untested]);

        $persisted = $this->persistedData($directory);

        $this->assertCount(2, $persisted['sourceFiles']);
        $this->assertContains($untested, $persisted['files']);
    }

    public function testHasNoRecordingWhenNothingWasPersisted(): void
    {
        $this->assertNull(new TestImpactDataFile($this->temporaryDirectory(), $this->assumptions())->recording());
    }

    public function testHasARecordingOfWhatWasPersisted(): void
    {
        $directory = $this->temporaryDirectory();
        $covered   = $this->writeSourceFile($directory, 'Covered', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$covered]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, [$covered]);

        $recording = new TestImpactDataFile($directory, $this->assumptions())->recording();

        $this->assertNotNull($recording);
        $this->assertTrue($recording->knows('FooTest::testOne'));
    }

    public function testDiscardsWhatWasRecordedFromSomethingElseThanWhatIsBeingRecorded(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');
        $bar       = $this->writeSourceFile($directory, 'Bar', 'first');

        $observed = new DefaultTestImpactData;
        $observed->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($observed, Provenance::ObservedExecution, []);

        $declared = new DefaultTestImpactData;
        $declared->record('BarTest::testOne', [$bar]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($declared, Provenance::CoverageTargets, []);

        $persisted = $this->persistedData($directory);

        $this->assertSame('coverage-targets', $persisted['provenance']);
        $this->assertSame([['BarTest::testOne', 'Bar.php']], $this->dependencies($persisted));
    }

    public function testKnowsThatWhatIsRecordedWasDerivedFromCoverageTargets(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::CoverageTargets, []);

        $this->assertTrue(new TestImpactDataFile($directory, $this->assumptions())->testsThatDependOn($foo)->wereDerivedFromCoverageTargets());
    }

    public function testKnowsNoTestExecutedASourceFileThatWasNeverRecorded(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $this->assertTrue(new TestImpactDataFile($directory, $this->assumptions())->testsThatDependOn($foo)->isEmpty());
    }

    public function testKnowsWhichTestsExecutedASourceFileAsItIsNow(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');
        $bar       = $this->writeSourceFile($directory, 'Bar', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$foo]);
        $data->record('BarTest::testOne', [$bar]);
        $data->record('BothTest::testOne', [$foo, $bar]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        $tests = new TestImpactDataFile($directory, $this->assumptions())->testsThatDependOn($foo);

        $this->assertSame(['BothTest::testOne', 'FooTest::testOne'], $tests->thatDependOnTheFileAsItIsNow());
        $this->assertSame([], $tests->thatDependOnAnEarlierVersionOfTheFile());
    }

    public function testKnowsWhichTestsExecutedAnEarlierVersionOfASourceFile(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);
        $first->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($second, Provenance::ObservedExecution, []);

        $tests = new TestImpactDataFile($directory, $this->assumptions())->testsThatDependOn($foo);

        $this->assertSame(['BarTest::testOne'], $tests->thatDependOnTheFileAsItIsNow());
        $this->assertSame(['FooTest::testOne'], $tests->thatDependOnAnEarlierVersionOfTheFile());
    }

    public function testKnowsEveryTestExecutedAnEarlierVersionOfASourceFileThatIsNoLongerThere(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        unlink($foo);

        $tests = new TestImpactDataFile($directory, $this->assumptions())->testsThatDependOn($foo);

        $this->assertSame([], $tests->thatDependOnTheFileAsItIsNow());
        $this->assertSame(['FooTest::testOne'], $tests->thatDependOnAnEarlierVersionOfTheFile());
    }

    private function assumptions(): Assumptions
    {
        return Assumptions::from(
            null,
            new Source(
                null,
                false,
                FilterDirectoryCollection::fromArray([]),
                FilterFileCollection::fromArray([]),
                FilterDirectoryCollection::fromArray([]),
                FilterFileCollection::fromArray([]),
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                [
                    'functions' => [],
                    'methods'   => [],
                ],
                false,
                false,
                false,
                true,
            ),
        );
    }

    /**
     * @return array{version: int, phpunit: string, php: int, files: list<string>, versions: list<array{0: int, 1: string}>, tests: array<string, list<int>>}
     */
    private function persistedData(string $directory): array
    {
        $contents = file_get_contents($directory . DIRECTORY_SEPARATOR . 'test-impact-data');

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);

        return $data;
    }

    /**
     * What a source file was recorded as being, looked up without knowing how
     * the files were numbered.
     *
     * @param non-empty-string $file
     *
     * @return non-empty-string
     */
    private function hashOfSourceFile(array $persisted, string $file): string
    {
        foreach ($persisted['sourceFiles'] as [$position, $hash]) {
            if ($persisted['files'][$position] !== $file) {
                continue;
            }

            $this->assertIsString($hash);
            $this->assertNotSame('', $hash);

            return $hash;
        }

        $this->fail($file . ' was not recorded as a source file');
    }

    /**
     * The test and the base name of each source file it executed, so that what
     * was persisted can be compared without knowing how files and versions
     * were numbered.
     *
     * @return list<array{0: string, 1: string}>
     */
    private function dependencies(array $persisted): array
    {
        $dependencies = [];

        foreach ($persisted['tests'] as $test => $versions) {
            foreach ($versions as $version) {
                $dependencies[] = [$test, basename($persisted['files'][$persisted['versions'][$version][0]])];
            }
        }

        sort($dependencies);

        return $dependencies;
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-impact-data-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @return non-empty-string
     */
    private function writeSourceFile(string $directory, string $name, string $contents): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . '.php';

        file_put_contents($file, '<?php declare(strict_types=1); // ' . $contents . PHP_EOL);

        return $file;
    }

    private static function assumptionsOfTheProvider(): array
    {
        return new self('assumptionsOfTheProvider')->assumptions()->asArray();
    }

    private static function withoutKey(array $data, string $key): array
    {
        unset($data[$key]);

        return $data;
    }
}
