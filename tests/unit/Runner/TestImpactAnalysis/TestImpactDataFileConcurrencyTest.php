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
use const PHP_BINARY;
use const PHP_EOL;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function proc_close;
use function proc_open;
use function realpath;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function usleep;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestIndex\FileHasher;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(TestImpactDataFile::class)]
#[UsesClass(Assumptions::class)]
#[UsesClass(DefaultTestImpactData::class)]
#[UsesClass(FileHasher::class)]
#[UsesClass(PathHasher::class)]
#[UsesClass(Recording::class)]
#[Medium]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class TestImpactDataFileConcurrencyTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            $this->deleteDirectory($directory);
        }

        $this->directories = [];
    }

    /**
     * What another test run wrote while this one was recording must not be
     * lost: a test run that reads what is there, adds what it recorded and
     * writes the result has to hold the file for as long as that takes.
     */
    public function testKeepsWhatAnotherTestRunWroteWhileThisOneWasRecording(): void
    {
        $directory  = $this->temporaryDirectory();
        $sourceFile = $this->writeSourceFile($directory, 'Covered');
        $dataFile   = $directory . DIRECTORY_SEPARATOR . 'test-impact-data';
        $marker     = $directory . DIRECTORY_SEPARATOR . 'locked';

        $whatIsThere = new DefaultTestImpactData;
        $whatIsThere->record('FooTest::testOne', [$sourceFile]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($whatIsThere, Provenance::ObservedExecution, [$sourceFile]);

        $whatTheOtherTestRunWrites = $this->whatAnotherTestRunRecords('BazTest::testOne', $sourceFile);

        $process = $this->holdTheFile($dataFile, $marker, $whatTheOtherTestRunWrites);

        $waited = 0;

        while (!file_exists($marker)) {
            usleep(1000);

            $waited++;

            $this->assertLessThan(10000, $waited, 'The other test run did not take the file');
        }

        $whatThisTestRunRecorded = new DefaultTestImpactData;
        $whatThisTestRunRecorded->record('BarTest::testOne', [$sourceFile]);

        new TestImpactDataFile($directory, $this->assumptions())->persist(
            $whatThisTestRunRecorded,
            Provenance::ObservedExecution,
            [$sourceFile],
        );

        proc_close($process);

        $recording = new TestImpactDataFile($directory, $this->assumptions())->recording(Provenance::ObservedExecution);

        $this->assertNotNull($recording);
        $this->assertTrue($recording->knows('BarTest::testOne'), 'What this test run recorded was not written');
        $this->assertTrue($recording->knows('BazTest::testOne'), 'What the other test run wrote was lost');
    }

    /**
     * The file another test run leaves behind when it records one test.
     *
     * @return non-empty-string the file it wrote
     */
    private function whatAnotherTestRunRecords(string $test, string $sourceFile): string
    {
        $directory = $this->temporaryDirectory();

        $data = new DefaultTestImpactData;
        $data->record($test, [$sourceFile]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, [$sourceFile]);

        return $directory . DIRECTORY_SEPARATOR . 'test-impact-data';
    }

    /**
     * Takes the file, says that it has it, keeps it for a while and then
     * writes what another test run recorded into it: a test run that reads the
     * file before taking it does not see what is written here, and writes what
     * it read over it.
     *
     * @return resource
     */
    private function holdTheFile(string $file, string $marker, string $contents)
    {
        $code = <<<'PHP'
            $handle = fopen($argv[1], 'c+');

            flock($handle, LOCK_EX);

            touch($argv[2]);

            usleep(500000);

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string) file_get_contents($argv[3]));

            flock($handle, LOCK_UN);
            fclose($handle);
            PHP;

        $process = proc_open([PHP_BINARY, '-r', $code, $file, $marker, $contents], [], $pipes);

        $this->assertIsResource($process);

        return $process;
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
            [],
        );
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
    private function writeSourceFile(string $directory, string $name): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . '.php';

        file_put_contents($file, '<?php declare(strict_types=1);' . PHP_EOL);

        return $file;
    }

    private function deleteDirectory(string $directory): void
    {
        $entries = scandir($directory);

        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $path = $directory . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($path)) {
                    $this->deleteDirectory($path);

                    continue;
                }

                unlink($path);
            }
        }

        rmdir($directory);
    }
}
