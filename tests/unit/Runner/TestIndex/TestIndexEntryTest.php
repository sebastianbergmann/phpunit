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
use function array_keys;
use function file_get_contents;
use function file_put_contents;
use function mkdir;
use function preg_replace;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Success;
use PHPUnit\TestFixture\TestIndexEntry\InheritingTest;
use PHPUnit\TestFixture\TestIndexEntry\ModifiedTest;
use PHPUnit\TestFixture\TestIndexEntry\RemovedTest;
use PHPUnit\TestFixture\TestIndexEntry\RootedTest;
use PHPUnit\TestFixture\TestIndexEntry\SimpleTest;
use PHPUnit\TestFixture\TestIndexEntry\UnchangedTest;
use PHPUnit\TestFixture\TestIndexEntry\UnreadableTest;
use PHPUnit\TestFixture\TestIndexEntry\WithHelperTest;
use ReflectionClass;

#[CoversClass(TestIndexEntry::class)]
#[UsesClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class TestIndexEntryTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

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

    public function testKnowsClassNameAndGroupsOfTestMethods(): void
    {
        $file  = $this->writeTestClass('Simple');
        $entry = TestIndexEntry::for(new ReflectionClass(SimpleTest::class), new FileHasher, false);

        $this->assertNotNull($entry);
        $this->assertSame(SimpleTest::class, $entry->className());
        $this->assertSame(['testOne'], array_keys($entry->groups()));
        $this->assertSame(['small', 'a-group'], $entry->groups()['testOne']);
        $this->assertArrayHasKey($file, $entry->dependencies());
    }

    #[TestDox('Does not record a method that is not a test method')]
    public function testDoesNotRecordMethodThatIsNotTestMethod(): void
    {
        $this->writeTestClass('WithHelper', "    public function helper(): void\n    {\n    }\n");

        $entry = TestIndexEntry::for(new ReflectionClass(WithHelperTest::class), new FileHasher, false);

        $this->assertNotNull($entry);
        $this->assertArrayNotHasKey('helper', $entry->groups());
    }

    #[TestDox('Depends on the file of a parent class and on the file of a trait')]
    public function testDependsOnFileOfParentClassAndOnFileOfTrait(): void
    {
        $files = $this->writeTestClassWithParentAndTrait('Inheriting');
        $entry = TestIndexEntry::for(new ReflectionClass(InheritingTest::class), new FileHasher, false);

        $this->assertNotNull($entry);

        foreach ($files as $file) {
            $this->assertArrayHasKey($file, $entry->dependencies());
        }
    }

    #[TestDox('Does not depend on the files of PHPUnit\Framework\TestCase and PHPUnit\Framework\Assert')]
    public function testDoesNotDependOnFilesOfTestCaseAndAssert(): void
    {
        $file = $this->writeTestClass('Rooted');

        $entry = TestIndexEntry::for(new ReflectionClass(RootedTest::class), new FileHasher, false);

        $this->assertNotNull($entry);

        /*
         * Neither of them can contribute a test method, so a change to PHPUnit
         * itself must not invalidate every entry there is.
         */
        $this->assertSame([$file], array_keys($entry->dependencies()));
    }

    public function testIsValidWhileSourceFilesAreUnchanged(): void
    {
        $this->writeTestClass('Unchanged');

        $entry = TestIndexEntry::for(new ReflectionClass(UnchangedTest::class), new FileHasher, false);

        $this->assertNotNull($entry);
        $this->assertTrue($entry->isValid(new FileHasher));
    }

    public function testIsNotValidWhenSourceFileChanged(): void
    {
        $file = $this->writeTestClass('Modified');

        $entry = TestIndexEntry::for(new ReflectionClass(ModifiedTest::class), new FileHasher, false);

        $this->assertNotNull($entry);

        $this->renameGroupIn($file);

        $this->assertFalse($entry->isValid(new FileHasher));
    }

    public function testIsNotValidWhenSourceFileWasRemoved(): void
    {
        $file = $this->writeTestClass('Removed');

        $entry = TestIndexEntry::for(new ReflectionClass(RemovedTest::class), new FileHasher, false);

        $this->assertNotNull($entry);

        unlink($file);

        $this->assertFalse($entry->isValid(new FileHasher));
    }

    #[TestDox('Cannot be created when a source file cannot be read')]
    public function testCannotBeCreatedWhenSourceFileCannotBeRead(): void
    {
        $file = $this->writeTestClass('Unreadable');

        $class = new ReflectionClass(UnreadableTest::class);

        unlink($file);

        $this->assertNull(TestIndexEntry::for($class, new FileHasher, false));
    }

    public function testCanBeCreatedFromRecordedValues(): void
    {
        $entry = TestIndexEntry::from(
            Success::class,
            ['testOne' => ['a-group']],
            ['testOne' => true],
            true,
            [__FILE__ => 'a-hash'],
        );

        $this->assertSame(Success::class, $entry->className());
        $this->assertSame(['testOne' => ['a-group']], $entry->groups());
        $this->assertSame(['testOne' => true], $entry->dataSets());
        $this->assertTrue($entry->madePhpUnitWarn());
        $this->assertSame([__FILE__ => 'a-hash'], $entry->dependencies());
    }

    /**
     * @return non-empty-string
     */
    private function directory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-index-entry-' . uniqid();

        mkdir($directory);

        $this->directories[] = $directory;

        return $directory;
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestClass(string $name, string $additionalMethods = ''): string
    {
        $file = $this->directory() . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndexEntry;

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

                {$additionalMethods}}
                PHP,
        );

        require_once $file;

        return $file;
    }

    /**
     * @param non-empty-string $name
     *
     * @return array{class: non-empty-string, parent: non-empty-string, trait: non-empty-string}
     */
    private function writeTestClassWithParentAndTrait(string $name): array
    {
        $directory  = $this->directory();
        $traitFile  = $directory . DIRECTORY_SEPARATOR . $name . 'Trait.php';
        $parentFile = $directory . DIRECTORY_SEPARATOR . $name . 'Parent.php';
        $classFile  = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $traitFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndexEntry;

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

        file_put_contents(
            $parentFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndexEntry;

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

        file_put_contents(
            $classFile,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndexEntry;

                final class {$name}Test extends {$name}Parent
                {
                    use {$name}Trait;

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
     * @param non-empty-string $file
     */
    private function renameGroupIn(string $file): void
    {
        $contents = file_get_contents($file);

        $this->assertIsString($contents);

        $changed = preg_replace('/Group\(\'./', "Group('z", $contents, 1, $count);

        $this->assertIsString($changed);
        $this->assertSame(1, $count);

        file_put_contents($file, $changed);
    }
}
