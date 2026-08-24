<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestImpactAnalysis\Assumptions;
use PHPUnit\Runner\TestImpactAnalysis\DefaultTestImpactData;
use PHPUnit\Runner\TestImpactAnalysis\Provenance;
use PHPUnit\Runner\TestImpactAnalysis\RecordedTests;
use PHPUnit\Runner\TestImpactAnalysis\TestImpactDataFile;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(ListTestsThatDependOnCommand::class)]
#[UsesClass(DefaultTestImpactData::class)]
#[UsesClass(RecordedTests::class)]
#[UsesClass(TestImpactDataFile::class)]
#[Small]
#[Group('textui')]
#[Group('textui/commands')]
final class ListTestsThatDependOnCommandTest extends TestCase
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

    public function testFailsWhenTheSourceFileDoesNotExist(): void
    {
        $directory = $this->temporaryDirectory();

        $result = new ListTestsThatDependOnCommand(
            new TestImpactDataFile($directory, $this->assumptions()),
            $directory . DIRECTORY_SEPARATOR . 'DoesNotExist.php',
        )->execute();

        $this->assertStringContainsString('does not exist', $result->output());
        $this->assertSame(Result::FAILURE, $result->shellExitCode());
    }

    public function testReportsThatNoTestIsRecordedForTheSourceFile(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $result = new ListTestsThatDependOnCommand(new TestImpactDataFile($directory, $this->assumptions()), $file)->execute();

        $this->assertSame('No test that depends on ' . $file . ' is recorded' . PHP_EOL, $result->output());
        $this->assertSame(Result::SUCCESS, $result->shellExitCode());
    }

    public function testListsTheTestsThatDependOnTheFileAsItIsNow(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$file]);
        $data->record('BarTest::testOne', [$file]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::ObservedExecution, []);

        $result = new ListTestsThatDependOnCommand(new TestImpactDataFile($directory, $this->assumptions()), $file)->execute();

        $this->assertSame(
            'Recorded from what the tests executed.' . PHP_EOL . PHP_EOL .
            'Tests that depend on ' . $file . ' as it is now:' . PHP_EOL .
            ' - BarTest::testOne' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL . PHP_EOL,
            $result->output(),
        );

        $this->assertSame(Result::SUCCESS, $result->shellExitCode());
    }

    public function testListsTheTestsThatDependOnAnEarlierVersionOfTheFileSeparately(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$file]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($first, Provenance::ObservedExecution, []);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$file]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($second, Provenance::ObservedExecution, []);

        $result = new ListTestsThatDependOnCommand(new TestImpactDataFile($directory, $this->assumptions()), $file)->execute();

        $this->assertSame(
            'Recorded from what the tests executed.' . PHP_EOL . PHP_EOL .
            'Tests that depend on ' . $file . ' as it is now:' . PHP_EOL .
            ' - BarTest::testOne' . PHP_EOL . PHP_EOL .
            'Tests that depend on an earlier version of ' . $file . ':' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL . PHP_EOL,
            $result->output(),
        );
    }

    public function testReportsWhatWasDerivedFromCoverageTargetsAsSuch(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$file]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::CoverageTargets, []);

        $result = new ListTestsThatDependOnCommand(new TestImpactDataFile($directory, $this->assumptions()), $file)->execute();

        $this->assertSame(
            'Recorded from the code coverage targets the tests declare.' . PHP_EOL . PHP_EOL .
            'Tests that depend on ' . $file . ' as it is now:' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL . PHP_EOL,
            $result->output(),
        );
    }

    public function testReportsThatNoTestDeclaresTheSourceFileAsATarget(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');
        $other     = $this->writeSourceFile($directory, 'Bar', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$other]);

        new TestImpactDataFile($directory, $this->assumptions())->persist($data, Provenance::CoverageTargets, []);

        $result = new ListTestsThatDependOnCommand(new TestImpactDataFile($directory, $this->assumptions()), $file)->execute();

        $this->assertSame(
            'No test that depends on ' . $file . ' is recorded' . PHP_EOL,
            $result->output(),
        );
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
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-list-tests-that-depend-on-' . uniqid();

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
}
