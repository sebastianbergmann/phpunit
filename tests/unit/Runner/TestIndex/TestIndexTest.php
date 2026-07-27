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
use function array_merge;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;
use function mkdir;
use function preg_replace;
use function rmdir;
use function scandir;
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
                'version' => 3,
                'phpunit' => Version::id(),
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
            'without keys'                   => [['version' => 3]],
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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\PlainTest'), false);

        $entry = $index->entryFor($file);

        $this->assertNotNull($entry);
        $this->assertSame('PHPUnit\TestFixture\TestIndex\PlainTest', $entry->className());
        $this->assertSame(['small', 'a-group'], $entry->groups()['testOne']);
    }

    #[TestDox('Invalidates an entry when the test file itself changes')]
    public function testInvalidatesEntryWhenTestFileChanges(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writePlainTestClass($directory, 'ChangedFile');

        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\ChangedFileTest'), false);
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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\ChangedParentTest'), false);
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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\ChangedTraitTest'), false);
        $index->persist();

        $this->assertNotNull($this->loadedIndex($indexDirectory)->entryFor($files['class']));

        $this->changeGroupNameIn($files['trait']);

        $this->assertNull($this->loadedIndex($indexDirectory)->entryFor($files['class']));
    }

    #[TestDox('Records the test methods a test class inherits from a parent class and a trait')]
    public function testRecordsInheritedTestMethods(): void
    {
        $directory = $this->temporaryDirectory();
        $files     = $this->writeTestClassWithParentAndTrait($directory, 'Inherited');

        $index = new TestIndex($this->temporaryDirectory());
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\InheritedTest'), false);

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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\RemovedDependencyTest'), false);
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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\PersistedTest'), false);
        $index->persist();

        $loaded = new TestIndex($indexDirectory);
        $loaded->load();

        $entry = $loaded->entryFor($file);

        $this->assertNotNull($entry);
        $this->assertSame('PHPUnit\TestFixture\TestIndex\PersistedTest', $entry->className());
        $this->assertSame(['small', 'a-group'], $entry->groups()['testOne']);
    }

    #[TestDox('Writes back entries that were not recorded again, so a run that skipped a file does not forget it')]
    public function testWritesBackEntriesThatWereNotRecordedAgain(): void
    {
        $directory      = $this->temporaryDirectory();
        $skipped        = $this->writePlainTestClass($directory, 'Skipped');
        $recorded       = $this->writePlainTestClass($directory, 'Recorded');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\SkippedTest'), false);
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\RecordedTest'), false);
        $index->persist();

        // a later run that skipped one of the files and only recorded the other
        $second = new TestIndex($indexDirectory);
        $second->load();
        $second->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\RecordedTest'), false);
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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\VanishedTest'), false);

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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\OtherVersionTest'), false);
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

    #[TestDox('Discards an index that was written in a different format')]
    public function testDiscardsIndexWrittenInDifferentFormat(): void
    {
        $directory      = $this->temporaryDirectory();
        $file           = $this->writePlainTestClass($directory, 'OtherFormat');
        $indexDirectory = $this->temporaryDirectory();

        $index = new TestIndex($indexDirectory);
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\OtherFormatTest'), false);
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
        $index->record(new ReflectionClass('PHPUnit\TestFixture\TestIndex\EvaluatedTest'), false);
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

        $class = new ReflectionClass('PHPUnit\TestFixture\TestIndex\UnreadableTest');

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

        $this->directories[] = $directory;

        return $directory;
    }

    /**
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writePlainTestClass(string $directory, string $name): string
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
                    #[Group('a-group')]
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
