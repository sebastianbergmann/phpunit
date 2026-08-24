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
use const PHP_OS_FAMILY;
use const PHP_VERSION_ID;
use function array_merge;
use function array_search;
use function chmod;
use function file_get_contents;
use function file_put_contents;
use function is_readable;
use function json_decode;
use function json_encode;
use function mkdir;
use function octdec;
use function preg_replace;
use function realpath;
use function rmdir;
use function scandir;
use function str_replace;
use function strlen;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\DirectoryDoesNotExistException;
use PHPUnit\Runner\Version;
use PHPUnit\TestFixture\TestIndex\ChangedDataProviderTest;
use PHPUnit\TestFixture\TestIndex\ChangedFileTest;
use PHPUnit\TestFixture\TestIndex\ChangedParentTest;
use PHPUnit\TestFixture\TestIndex\ChangedTraitTest;
use PHPUnit\TestFixture\TestIndex\EvaluatedTest;
use PHPUnit\TestFixture\TestIndex\InheritedTest;
use PHPUnit\TestFixture\TestIndex\InvalidGroupTest;
use PHPUnit\TestFixture\TestIndex\NumericGroupTest;
use PHPUnit\TestFixture\TestIndex\OtherFormatTest;
use PHPUnit\TestFixture\TestIndex\OtherPhpVersionTest;
use PHPUnit\TestFixture\TestIndex\OtherVersionTest;
use PHPUnit\TestFixture\TestIndex\PersistedTest;
use PHPUnit\TestFixture\TestIndex\PlainTest;
use PHPUnit\TestFixture\TestIndex\RecordedTest;
use PHPUnit\TestFixture\TestIndex\RemovedDependencyTest;
use PHPUnit\TestFixture\TestIndex\SkippedTest;
use PHPUnit\TestFixture\TestIndex\UnreadableIndexTest;
use PHPUnit\TestFixture\TestIndex\UnreadableTest;
use PHPUnit\TestFixture\TestIndex\UnusableGroupTest;
use PHPUnit\TestFixture\TestIndex\UsableGroupTest;
use PHPUnit\TestFixture\TestIndex\ValidGroupTest;
use PHPUnit\TestFixture\TestIndex\VanishedTest;
use ReflectionClass;

#[CoversClass(TestIndex::class)]
#[UsesClass(TestIndexEntry::class)]
#[UsesClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class TestIndexTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

    public static function provideMalformedIndexData(): array
    {
        $index = static function (mixed $groups, mixed $entries): array
        {
            return [
                'version' => 5,
                'phpunit' => Version::id(),
                'php'     => PHP_VERSION_ID,
                'groups'  => $groups,
                'entries' => $entries,
            ];
        };

        /**
         * @param array<string, mixed> $overrides
         */
        $entry = static function (array $overrides): array
        {
            return array_merge(
                [
                    'class'        => 'A',
                    'groups'       => [],
                    'dataSets'     => [],
                    'warned'       => false,
                    'dependencies' => [__FILE__ => 'a-hash'],
                ],
                $overrides,
            );
        };

        return [
            'not an array'                   => ['a string'],
            'without keys'                   => [['version' => 5]],
            'written in a different format'  => [$index([], []) + ['version' => 4711]],
            'group table is not an array'    => [$index('a string', [])],
            'group name is not a string'     => [$index([4711], [])],
            'group name is empty'            => [$index([''], [])],
            'entries is not an array'        => [$index([], 'a string')],
            'entry is not an array'          => [$index([], [__FILE__ => 'a string'])],
            'entry without keys'             => [$index([], [__FILE__ => ['class' => 'A']])],
            'class name is empty'            => [$index(['a'], [__FILE__ => $entry(['class' => ''])])],
            'groups is not an array'         => [$index(['a'], [__FILE__ => $entry(['groups' => 'a string'])])],
            'method name is empty'           => [$index(['a'], [__FILE__ => $entry(['groups' => ['' => [0]]])])],
            'group list is not an array'     => [$index(['a'], [__FILE__ => $entry(['groups' => ['testOne' => 0]])])],
            'group index is unknown'         => [$index(['a'], [__FILE__ => $entry(['groups' => ['testOne' => [4711]]])])],
            'group index is not an integer'  => [$index(['a'], [__FILE__ => $entry(['groups' => ['testOne' => ['a']]])])],
            'data sets is not an array'      => [$index(['a'], [__FILE__ => $entry(['dataSets' => 'a string'])])],
            'data set method name is empty'  => [$index(['a'], [__FILE__ => $entry(['dataSets' => ['' => false]])])],
            'data set flag is not a boolean' => [$index(['a'], [__FILE__ => $entry(['dataSets' => ['testOne' => 'yes']])])],
            'warned flag is not a boolean'   => [$index(['a'], [__FILE__ => $entry(['warned' => 'yes'])])],
            'dependencies is not an array'   => [$index(['a'], [__FILE__ => $entry(['dependencies' => 'a string'])])],
            'dependency hash is empty'       => [$index(['a'], [__FILE__ => $entry(['dependencies' => [__FILE__ => '']])])],
            'without dependencies'           => [$index(['a'], [__FILE__ => $entry(['dependencies' => []])])],
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

    public function testDoesNotHandOutEntryForFileThatIsNotIndexed(): void
    {
        $index = new TestIndex($this->temporaryDirectory());

        $this->assertNull($index->entryFor(__FILE__));
    }

    public function testHandsOutEntryForFileThatWasRecorded(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writePlainTestClass($directory, 'Plain');

        $index = new TestIndex($this->temporaryDirectory());
        $index->record(new ReflectionClass(PlainTest::class), false);

        $entry = $index->entryFor($file);

        $this->assertNotNull($entry);
        $this->assertSame(PlainTest::class, $entry->className());
        $this->assertSame(['small', 'a-group'], $entry->groups()['testOne']);
    }

    #[TestDox('Invalidates an entry when the test file itself changes')]
    public function testInvalidatesEntryWhenTestFileChanges(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writePlainTestClass($directory, 'ChangedFile');

        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(ChangedFileTest::class), false);
        $index->persist();

        $this->assertNotNull($this->loadedIndex($indexDirectory)->entryFor($file));

        $this->changeGroupNameIn($file);

        $this->assertNull($this->loadedIndex($indexDirectory)->entryFor($file));
    }

    #[TestDox('Invalidates an entry when the file of a parent class changes')]
    public function testInvalidatesEntryWhenFileOfParentClassChanges(): void
    {
        $directory = $this->temporaryDirectory();
        $files     = $this->writeTestClassWithParentAndTrait($directory, 'ChangedParent');

        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(ChangedParentTest::class), false);
        $index->persist();

        $this->assertNotNull($this->loadedIndex($indexDirectory)->entryFor($files['class']));

        $this->changeGroupNameIn($files['parent']);

        $this->assertNull($this->loadedIndex($indexDirectory)->entryFor($files['class']));
    }

    #[TestDox('Invalidates an entry when the file of a trait changes')]
    public function testInvalidatesEntryWhenFileOfTraitChanges(): void
    {
        $directory = $this->temporaryDirectory();
        $files     = $this->writeTestClassWithParentAndTrait($directory, 'ChangedTrait');

        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(ChangedTraitTest::class), false);
        $index->persist();

        $this->assertNotNull($this->loadedIndex($indexDirectory)->entryFor($files['class']));

        $this->changeGroupNameIn($files['trait']);

        $this->assertNull($this->loadedIndex($indexDirectory)->entryFor($files['class']));
    }

    #[TestDox('Invalidates an entry when the file of an external data provider changes')]
    public function testInvalidatesEntryWhenFileOfExternalDataProviderChanges(): void
    {
        $directory = $this->temporaryDirectory();
        $files     = $this->writeTestClassWithExternalDataProvider($directory, 'ChangedDataProvider');

        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(ChangedDataProviderTest::class), false);
        $index->persist();

        $entry = $this->loadedIndex($indexDirectory)->entryFor($files['class']);

        $this->assertNotNull($entry);
        $this->assertArrayHasKey($files['provider'], $entry->dependencies());

        file_put_contents(
            $files['provider'],
            str_replace('[1, 2, 3]', '[4, 5, 9]', (string) file_get_contents($files['provider'])),
        );

        $this->assertNull($this->loadedIndex($indexDirectory)->entryFor($files['class']));
    }

    #[TestDox('Records the test methods a test class inherits from a parent class and a trait')]
    public function testRecordsInheritedTestMethods(): void
    {
        $directory = $this->temporaryDirectory();
        $files     = $this->writeTestClassWithParentAndTrait($directory, 'Inherited');

        $index = new TestIndex($this->temporaryDirectory());
        $index->record(new ReflectionClass(InheritedTest::class), false);

        $entry = $index->entryFor($files['class']);

        $this->assertNotNull($entry);
        $this->assertArrayHasKey('testInClass', $entry->groups());
        $this->assertArrayHasKey('testInParent', $entry->groups());
        $this->assertArrayHasKey('testInTrait', $entry->groups());
        $this->assertContains('from-parent', $entry->groups()['testInParent']);
        $this->assertContains('from-trait', $entry->groups()['testInTrait']);

        $this->assertArrayHasKey($files['class'], $entry->dependencies());
        $this->assertArrayHasKey($files['parent'], $entry->dependencies());
        $this->assertArrayHasKey($files['trait'], $entry->dependencies());
    }

    public function testInvalidatesEntryWhenDependencyIsRemoved(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writePlainTestClass($directory, 'RemovedDependency');

        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(RemovedDependencyTest::class), false);
        $index->persist();

        $this->assertNotNull($this->loadedIndex($indexDirectory)->entryFor($file));

        unlink($file);

        $this->assertNull($this->loadedIndex($indexDirectory)->entryFor($file));
    }

    public function testPersistsAndLoadsEntries(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'Persisted');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(PersistedTest::class), false);
        $index->persist();

        $loaded = new TestIndex($indexDirectory);
        $loaded->load();

        $entry = $loaded->entryFor($file);

        $this->assertNotNull($entry);
        $this->assertSame(PersistedTest::class, $entry->className());
        $this->assertSame(['small', 'a-group'], $entry->groups()['testOne']);
    }

    #[TestDox('Persists and loads a group name that looks like a number')]
    public function testPersistsAndLoadsNumericGroupName(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'NumericGroup', '6546');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(NumericGroupTest::class), false);
        $index->persist();

        $entry = $this->loadedIndex($indexDirectory)->entryFor($file);

        $this->assertNotNull($entry);
        $this->assertSame(['small', '6546'], $entry->groups()['testOne']);
    }

    #[TestDox('Keeps the entries that do not use a group name that cannot be read')]
    public function testKeepsEntriesThatDoNotUseGroupNameThatCannotBeRead(): void
    {
        $directory      = $this->temporaryDirectory();
        $usable         = $this->writePlainTestClass($directory, 'UsableGroup', 'usable');
        $unusable       = $this->writePlainTestClass($directory, 'UnusableGroup', 'unusable');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(UsableGroupTest::class), false);
        $index->record(new ReflectionClass(UnusableGroupTest::class), false);
        $index->persist();

        $indexFile = $indexDirectory . DIRECTORY_SEPARATOR . 'test-index';
        $contents  = file_get_contents($indexFile);

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);

        $position = array_search('unusable', $data['groups'], true);

        $this->assertIsInt($position);

        $data['groups'][$position] = 4711;

        file_put_contents($indexFile, json_encode($data));

        $loaded = $this->loadedIndex($indexDirectory);

        $this->assertNotNull($loaded->entryFor($usable));
        $this->assertNull($loaded->entryFor($unusable));
    }

    #[TestDox('Keeps the index that is already there when the new one cannot be written as JSON')]
    public function testKeepsExistingIndexWhenNewOneCannotBeWrittenAsJson(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'ValidGroup', 'valid');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(ValidGroupTest::class), false);
        $index->persist();

        // A later run also indexes a file whose group name is not valid UTF-8
        $this->writePlainTestClass($directory, 'InvalidGroup', "\xB1\x31");

        $second = new TestIndex($indexDirectory);
        $second->load();
        $second->record(new ReflectionClass(ValidGroupTest::class), false);
        $second->record(new ReflectionClass(InvalidGroupTest::class), false);
        $second->persist();

        $this->assertNotNull($this->loadedIndex($indexDirectory)->entryFor($file));
    }

    #[TestDox('Writes back entries that were not recorded again, so a run that skipped a file does not forget it')]
    public function testWritesBackEntriesThatWereNotRecordedAgain(): void
    {
        $directory      = $this->temporaryDirectory();
        $skipped        = $this->writePlainTestClass($directory, 'Skipped');
        $recorded       = $this->writePlainTestClass($directory, 'Recorded');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(SkippedTest::class), false);
        $index->record(new ReflectionClass(RecordedTest::class), false);
        $index->persist();

        // a later run that skipped one of the files and only recorded the other
        $second = new TestIndex($indexDirectory);
        $second->load();
        $second->record(new ReflectionClass(RecordedTest::class), false);
        $second->persist();

        $third = new TestIndex($indexDirectory);
        $third->load();

        $this->assertNotNull($third->entryFor($skipped));
        $this->assertNotNull($third->entryFor($recorded));
    }

    public function testDropsEntriesForFilesThatNoLongerExist(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'Vanished');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(VanishedTest::class), false);

        unlink($file);

        $index->persist();

        $contents = file_get_contents($indexDirectory . DIRECTORY_SEPARATOR . 'test-index');

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);
        $this->assertSame([], $data['entries']);
    }

    #[TestDox('Discards an index that was written by a different version of PHPUnit')]
    public function testDiscardsIndexWrittenByDifferentVersionOfPhpUnit(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'OtherVersion');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(OtherVersionTest::class), false);
        $index->persist();

        $indexFile = $indexDirectory . DIRECTORY_SEPARATOR . 'test-index';
        $contents  = file_get_contents($indexFile);

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);
        $this->assertSame(Version::id(), $data['phpunit']);

        $data['phpunit'] = $data['phpunit'] . '-not-really';

        file_put_contents($indexFile, json_encode($data));

        $loaded = new TestIndex($indexDirectory);
        $loaded->load();

        $this->assertNull($loaded->entryFor($file));
    }

    #[TestDox('Discards an index that was written by a different version of PHP')]
    public function testDiscardsIndexWrittenByDifferentVersionOfPhp(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'OtherPhpVersion');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(OtherPhpVersionTest::class), false);
        $index->persist();

        $indexFile = $indexDirectory . DIRECTORY_SEPARATOR . 'test-index';
        $contents  = file_get_contents($indexFile);

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);
        $this->assertSame(PHP_VERSION_ID, $data['php']);

        /*
         * The file the entry was derived from is unchanged: only the version of
         * PHP that read it is a different one.
         */
        $data['php'] = $data['php'] + 100;

        file_put_contents($indexFile, json_encode($data));

        $loaded = new TestIndex($indexDirectory);
        $loaded->load();

        $this->assertNull($loaded->entryFor($file));
    }

    #[TestDox('Discards an index that was written in a different format')]
    public function testDiscardsIndexWrittenInDifferentFormat(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'OtherFormat');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(OtherFormatTest::class), false);
        $index->persist();

        $indexFile = $indexDirectory . DIRECTORY_SEPARATOR . 'test-index';
        $contents  = file_get_contents($indexFile);

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);

        $data['version'] = 4711;

        file_put_contents($indexFile, json_encode($data));

        $loaded = new TestIndex($indexDirectory);
        $loaded->load();

        $this->assertNull($loaded->entryFor($file));
    }

    public function testIgnoresIndexFileThatCannotBeRead(): void
    {
        $indexDirectory = $this->temporaryDirectory();

        file_put_contents($indexDirectory . DIRECTORY_SEPARATOR . 'test-index', 'not json');

        $index = new TestIndex($indexDirectory);
        $index->load();

        $this->assertNull($index->entryFor(__FILE__));
    }

    #[TestDox('Ignores an index file whose contents cannot be read')]
    public function testIgnoresIndexFileWhoseContentsCannotBeRead(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'UnreadableIndex');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(UnreadableIndexTest::class), false);
        $index->persist();

        $indexFile = $indexDirectory . DIRECTORY_SEPARATOR . 'test-index';

        chmod($indexFile, octdec('0'));

        if (is_readable($indexFile)) {
            chmod($indexFile, octdec('644'));

            $this->markTestSkipped('The index file can still be read');
        }

        $loaded = new TestIndex($indexDirectory);

        // reading the index file warns, which is not what this test is about
        @$loaded->load();

        chmod($indexFile, octdec('644'));

        $this->assertNull($loaded->entryFor($file));
    }

    public function testIgnoresIndexFileThatDoesNotExist(): void
    {
        $index = new TestIndex($this->temporaryDirectory());
        $index->load();

        $this->assertNull($index->entryFor(__FILE__));
    }

    #[DataProvider('provideMalformedIndexData')]
    #[TestDox('Discards a malformed index: $_dataName')]
    public function testDiscardsMalformedIndex(mixed $data): void
    {
        $indexDirectory = $this->temporaryDirectory();

        file_put_contents(
            $indexDirectory . DIRECTORY_SEPARATOR . 'test-index',
            json_encode($data),
        );

        $this->assertNull($this->loadedIndex($indexDirectory)->entryFor(__FILE__));
    }

    #[TestDox('Does not record a class that was not declared in a file')]
    public function testDoesNotRecordClassThatWasNotDeclaredInFile(): void
    {
        eval('namespace PHPUnit\\TestFixture\\TestIndex; class EvaluatedTest extends \\PHPUnit\\Framework\\TestCase {}');

        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass(EvaluatedTest::class), false);
        $index->persist();

        $contents = file_get_contents($indexDirectory . DIRECTORY_SEPARATOR . 'test-index');

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);
        $this->assertSame([], $data['entries']);
    }

    #[TestDox('Does not record a class whose source file cannot be read')]
    public function testDoesNotRecordClassWhoseSourceFileCannotBeRead(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writePlainTestClass($directory, 'Unreadable');

        $class = new ReflectionClass(UnreadableTest::class);

        unlink($file);

        $index = new TestIndex($this->temporaryDirectory());
        $index->record($class, false);

        $this->assertNull($index->entryFor(__FILE__));
    }

    #[TestDox('Fails when the directory for the index cannot be created')]
    public function testFailsWhenDirectoryForIndexCannotBeCreated(): void
    {
        $file = $this->temporaryDirectory() . DIRECTORY_SEPARATOR . 'not-a-directory';

        file_put_contents($file, '');

        $index = new TestIndex($file . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'test-index');

        $this->expectException(DirectoryDoesNotExistException::class);

        $index->persist();
    }

    /**
     * A later run sees an index through a fresh instance: the hashes computed
     * during one run are remembered for the duration of that run, as the
     * contents of a source file cannot change while it is being used.
     *
     * @param non-empty-string $directory
     */
    private function loadedIndex(string $directory): TestIndex
    {
        $index = new TestIndex($directory);
        $index->load();

        return $index;
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-index-' . uniqid();

        mkdir($directory);

        /*
         * The path is resolved so that it is the path PHP itself reports for
         * the files in this directory: sys_get_temp_dir() can return a path
         * that is not resolved, a short (8.3) path on Windows for instance,
         * while reflection always reports the resolved one.
         */
        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @param non-empty-string $directory
     * @param non-empty-string $name
     * @param non-empty-string $group
     *
     * @return non-empty-string
     */
    private function writePlainTestClass(string $directory, string $name, string $group = 'a-group'): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                use PHPUnit\Framework\Attributes\Group;
                use PHPUnit\Framework\Attributes\Small;
                use PHPUnit\Framework\TestCase;

                #[Small]
                final class {$name}Test extends TestCase
                {
                    #[Group('{$group}')]
                    public function testOne(): void
                    {
                    }
                }
                PHP,
        );

        require_once $file;

        return $file;
    }

    /**
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return array{class: non-empty-string, parent: non-empty-string, trait: non-empty-string}
     */
    /**
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return array{class: non-empty-string, provider: non-empty-string}
     */
    private function writeTestClassWithExternalDataProvider(string $directory, string $name): array
    {
        $providerFile = $directory . DIRECTORY_SEPARATOR . $name . 'Provider.php';

        file_put_contents(
            $providerFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                final class {$name}Provider
                {
                    public static function provide(): array
                    {
                        return [[1, 2, 3]];
                    }
                }
                PHP,
        );

        $classFile = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $classFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                use PHPUnit\Framework\Attributes\DataProviderExternal;
                use PHPUnit\Framework\TestCase;

                final class {$name}Test extends TestCase
                {
                    #[DataProviderExternal({$name}Provider::class, 'provide')]
                    public function testOne(int \$a, int \$b, int \$c): void
                    {
                    }
                }
                PHP,
        );

        require_once $providerFile;

        require_once $classFile;

        return ['class' => $classFile, 'provider' => $providerFile];
    }

    private function writeTestClassWithParentAndTrait(string $directory, string $name): array
    {
        $traitFile = $directory . DIRECTORY_SEPARATOR . $name . 'Trait.php';

        file_put_contents(
            $traitFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                use PHPUnit\Framework\Attributes\Group;

                trait {$name}Trait
                {
                    #[Group('from-trait')]
                    public function testInTrait(): void
                    {
                    }
                }
                PHP,
        );

        $parentFile = $directory . DIRECTORY_SEPARATOR . $name . 'Parent.php';

        file_put_contents(
            $parentFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                use PHPUnit\Framework\Attributes\Group;
                use PHPUnit\Framework\TestCase;

                abstract class {$name}Parent extends TestCase
                {
                    #[Group('from-parent')]
                    public function testInParent(): void
                    {
                    }
                }
                PHP,
        );

        $classFile = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $classFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                use PHPUnit\Framework\Attributes\Group;

                final class {$name}Test extends {$name}Parent
                {
                    use {$name}Trait;

                    #[Group('from-class')]
                    public function testInClass(): void
                    {
                    }
                }
                PHP,
        );

        require_once $traitFile;

        require_once $parentFile;

        require_once $classFile;

        return ['class' => $classFile, 'parent' => $parentFile, 'trait' => $traitFile];
    }

    /**
     * Renames a group without changing the size of the file: this is the change
     * that a check of the modification time and the size of a file can miss,
     * and the reason the index is keyed by the contents of a file instead.
     *
     * @param non-empty-string $file
     */
    private function changeGroupNameIn(string $file): void
    {
        $contents = file_get_contents($file);

        $this->assertIsString($contents);

        $changed = preg_replace('/Group\(\'./', "Group('z", $contents, 1, $count);

        $this->assertIsString($changed);
        $this->assertSame(1, $count);
        $this->assertSame(strlen($contents), strlen($changed));

        file_put_contents($file, $changed);
    }
}
