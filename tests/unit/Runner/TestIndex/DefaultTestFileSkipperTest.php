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
use function json_decode;
use function mkdir;
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

#[CoversClass(DefaultTestFileSkipper::class)]
#[UsesClass(TestIndex::class)]
#[UsesClass(TestIndexEntry::class)]
#[UsesClass(GroupPruner::class)]
#[UsesClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class DefaultTestFileSkipperTest extends TestCase
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

    #[TestDox('Does not skip a file that is not indexed')]
    public function testDoesNotSkipFileThatIsNotIndexed(): void
    {
        $file = $this->writeTestClass('NotIndexed');

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
        );

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Skips an indexed file without a test in the selected group')]
    public function testSkipsIndexedFileWithoutTestInSelectedGroup(): void
    {
        $file = $this->writeTestClass('NotSelected');

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
        );

        $skipper->record($file);

        $this->assertTrue($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Does not skip an indexed file with a test in the selected group')]
    public function testDoesNotSkipIndexedFileWithTestInSelectedGroup(): void
    {
        $file = $this->writeTestClass('Selected');

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($this->directory()),
            new GroupPruner(['a-group'], []),
        );

        $skipper->record($file);

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Does not skip anything when no groups are selected')]
    public function testDoesNotSkipAnythingWhenNoGroupsAreSelected(): void
    {
        $file = $this->writeTestClass('NoSelection');

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($this->directory()),
            new GroupPruner([], []),
        );

        $skipper->record($file);

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Considers the groups configured for a test file in the XML configuration file')]
    public function testConsidersGroupsFromConfiguration(): void
    {
        $file = $this->writeTestClass('FromConfiguration');

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($this->directory()),
            new GroupPruner(['from-configuration'], []),
        );

        $skipper->record($file);

        $this->assertTrue($skipper->canSkipLoading($file, []));
        $this->assertFalse($skipper->canSkipLoading($file, ['from-configuration']));
    }

    #[TestDox('Does not index a file that does not contain a test class')]
    public function testDoesNotIndexFileThatDoesNotContainTestClass(): void
    {
        $directory = $this->directory();
        $file      = $directory . DIRECTORY_SEPARATOR . 'NotATest.php';

        file_put_contents($file, '<?php declare(strict_types=1);' . "\n");

        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
        );

        $skipper->record($file);
        $skipper->persist();

        $this->assertFalse($skipper->canSkipLoading($file, []));
        $this->assertSame([], $this->entriesIn($indexDirectory));
    }

    #[TestDox('Does not index a file again while what is known about it is still valid')]
    public function testDoesNotIndexFileAgainWhileWhatIsKnownAboutItIsStillValid(): void
    {
        $file           = $this->writeTestClass('AlreadyIndexed');
        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
        );

        $skipper->record($file);
        $skipper->persist();

        $before = file_get_contents($indexDirectory . DIRECTORY_SEPARATOR . 'test-index');

        $skipper->record($file);
        $skipper->persist();

        $this->assertSame($before, file_get_contents($indexDirectory . DIRECTORY_SEPARATOR . 'test-index'));
    }

    public function testWritesTheIndex(): void
    {
        $file           = $this->writeTestClass('Written');
        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
        );

        $skipper->record($file);
        $skipper->persist();

        $this->assertSame([$file], array_keys($this->entriesIn($indexDirectory)));
    }

    /**
     * @param non-empty-string $directory
     *
     * @return array<string, mixed>
     */
    private function entriesIn(string $directory): array
    {
        $contents = file_get_contents($directory . DIRECTORY_SEPARATOR . 'test-index');

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);
        $this->assertIsArray($data['entries']);

        return $data['entries'];
    }

    /**
     * @return non-empty-string
     */
    private function directory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-skipper-' . uniqid();

        mkdir($directory);

        $this->directories[] = $directory;

        return $directory;
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestClass(string $name): string
    {
        $file = $this->directory() . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestFileSkipper;

                use PHPUnit\Framework\Attributes\Group;
                use PHPUnit\Framework\TestCase;

                final class {$name}Test extends TestCase
                {
                    #[Group('a-group')]
                    public function testOne(): void
                    {
                    }
                }
                PHP,
        );

        return $file;
    }
}
