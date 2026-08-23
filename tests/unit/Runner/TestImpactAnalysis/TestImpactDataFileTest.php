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

#[CoversClass(TestImpactDataFile::class)]
#[UsesClass(DefaultTestImpactData::class)]
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
            'version'  => 1,
            'phpunit'  => Version::id(),
            'php'      => PHP_VERSION_ID,
            'files'    => ['/src/Foo.php'],
            'versions' => [[0, 'a-hash']],
            'tests'    => ['FooTest::testOne' => [0]],
        ];

        return [
            'written by another version of PHPUnit' => [['phpunit' => 'another-version'] + $usable],
            'written by another version of PHP'     => [['php' => PHP_VERSION_ID - 1] + $usable],
            'written in another format'             => [['version' => 0] + $usable],
            'without files'                         => [self::withoutKey($usable, 'files')],
            'without versions'                      => [self::withoutKey($usable, 'versions')],
            'without tests'                         => [self::withoutKey($usable, 'tests')],
            'with files that are not an array'      => [['files' => 'not-an-array'] + $usable],
            'with versions that are not an array'   => [['versions' => 'not-an-array'] + $usable],
            'with tests that are not an array'      => [['tests' => 'not-an-array'] + $usable],
            'with files that are not a list'        => [['files' => ['a-key' => '/src/Foo.php']] + $usable],
            'with a file that is not a string'      => [['files' => [1]] + $usable],
            'with an empty file name'               => [['files' => ['']] + $usable],
            'with a version that is not a pair'     => [['versions' => [[0]]] + $usable],
            'with a version of an unknown file'     => [['versions' => [[1, 'a-hash']]] + $usable],
            'with a hash that is not a string'      => [['versions' => [[0, 1]]] + $usable],
            'with an empty hash'                    => [['versions' => [[0, '']]] + $usable],
            'with an empty test name'               => [['tests' => ['' => [0]]] + $usable],
            'with a test that is not a list'        => [['tests' => ['FooTest::testOne' => 0]] + $usable],
            'with an unknown version'               => [['tests' => ['FooTest::testOne' => [1]]] + $usable],
            'with a version that is not an integer' => [['tests' => ['FooTest::testOne' => ['0']]] + $usable],
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

        new TestImpactDataFile($directory)->persist($data);

        $persisted = $this->persistedData($directory);

        $this->assertSame(1, $persisted['version']);
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

        new TestImpactDataFile($directory)->persist($data);

        $this->assertSame($directory . DIRECTORY_SEPARATOR . 'Foo.php', $this->persistedData($directory)['files'][0]);
    }

    public function testPersistsIntoTheFileWhenOneIsNamedInsteadOfADirectory(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$file]);

        new TestImpactDataFile($directory . DIRECTORY_SEPARATOR . 'named-file')->persist($data);

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

        new TestImpactDataFile($directory)->persist($first);

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$bar, $foo]);

        new TestImpactDataFile($directory)->persist($second);

        $this->assertSame(
            [
                ['BarTest::testOne', 'Bar.php'],
                ['BarTest::testOne', 'Foo.php'],
                ['FooTest::testOne', 'Foo.php'],
            ],
            $this->dependencies($this->persistedData($directory)),
        );
    }

    public function testRecordsTheVersionOfASourceFileThatATestExecuted(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);
        $first->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($first);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($second);

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

        new TestImpactDataFile($directory)->persist($first);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($second);

        $this->assertCount(1, $this->persistedData($directory)['versions']);
        $this->assertCount(1, $this->persistedData($directory)['files']);
    }

    public function testDoesNotRecordATestThatExecutedASourceFileThatCannotBeHashed(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($first);

        $second = new DefaultTestImpactData;
        $second->record('FooTest::testOne', [$foo, $directory . DIRECTORY_SEPARATOR . 'DoesNotExist.php']);

        new TestImpactDataFile($directory)->persist($second);

        $this->assertSame([], $this->persistedData($directory)['tests']);
    }

    public function testDiscardsWhatCannotBeReadAsJson(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        file_put_contents($directory . DIRECTORY_SEPARATOR . 'test-impact-data', 'this is not JSON');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($data);

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

        new TestImpactDataFile($directory)->persist($data);

        $this->assertSame([['FooTest::testOne', 'Foo.php']], $this->dependencies($this->persistedData($directory)));
    }

    public function testThrowsExceptionWhenTheDirectoryToPersistIntoCannotBeCreated(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $this->expectException(DirectoryDoesNotExistException::class);

        new TestImpactDataFile($file . DIRECTORY_SEPARATOR . 'test-impact-data')->persist(new DefaultTestImpactData);
    }

    public function testKnowsNoTestExecutedASourceFileThatWasNeverRecorded(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $this->assertTrue(new TestImpactDataFile($directory)->testsThatExecuted($foo)->isEmpty());
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

        new TestImpactDataFile($directory)->persist($data);

        $tests = new TestImpactDataFile($directory)->testsThatExecuted($foo);

        $this->assertSame(['BothTest::testOne', 'FooTest::testOne'], $tests->thatExecutedTheFileAsItIsNow());
        $this->assertSame([], $tests->thatExecutedAnEarlierVersionOfTheFile());
    }

    public function testKnowsWhichTestsExecutedAnEarlierVersionOfASourceFile(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$foo]);
        $first->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($first);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($second);

        $tests = new TestImpactDataFile($directory)->testsThatExecuted($foo);

        $this->assertSame(['BarTest::testOne'], $tests->thatExecutedTheFileAsItIsNow());
        $this->assertSame(['FooTest::testOne'], $tests->thatExecutedAnEarlierVersionOfTheFile());
    }

    public function testKnowsEveryTestExecutedAnEarlierVersionOfASourceFileThatIsNoLongerThere(): void
    {
        $directory = $this->temporaryDirectory();
        $foo       = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$foo]);

        new TestImpactDataFile($directory)->persist($data);

        unlink($foo);

        $tests = new TestImpactDataFile($directory)->testsThatExecuted($foo);

        $this->assertSame([], $tests->thatExecutedTheFileAsItIsNow());
        $this->assertSame(['FooTest::testOne'], $tests->thatExecutedAnEarlierVersionOfTheFile());
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

    private static function withoutKey(array $data, string $key): array
    {
        unset($data[$key]);

        return $data;
    }
}
