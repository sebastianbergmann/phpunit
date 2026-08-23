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
use PHPUnit\Runner\TestImpactAnalysis\DefaultTestImpactData;
use PHPUnit\Runner\TestImpactAnalysis\RecordedTests;
use PHPUnit\Runner\TestImpactAnalysis\TestImpactDataFile;

#[CoversClass(ListTestsThatExecutedCommand::class)]
#[UsesClass(DefaultTestImpactData::class)]
#[UsesClass(RecordedTests::class)]
#[UsesClass(TestImpactDataFile::class)]
#[Small]
#[Group('textui')]
#[Group('textui/commands')]
final class ListTestsThatExecutedCommandTest extends TestCase
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

        $result = new ListTestsThatExecutedCommand(
            new TestImpactDataFile($directory),
            $directory . DIRECTORY_SEPARATOR . 'DoesNotExist.php',
        )->execute();

        $this->assertStringContainsString('does not exist', $result->output());
        $this->assertSame(Result::FAILURE, $result->shellExitCode());
    }

    public function testReportsThatNoTestIsRecordedForTheSourceFile(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $result = new ListTestsThatExecutedCommand(new TestImpactDataFile($directory), $file)->execute();

        $this->assertSame('No test that executed ' . $file . ' is recorded' . PHP_EOL, $result->output());
        $this->assertSame(Result::SUCCESS, $result->shellExitCode());
    }

    public function testListsTheTestsThatExecutedTheSourceFileAsItIsNow(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $data = new DefaultTestImpactData;
        $data->record('FooTest::testOne', [$file]);
        $data->record('BarTest::testOne', [$file]);

        new TestImpactDataFile($directory)->persist($data);

        $result = new ListTestsThatExecutedCommand(new TestImpactDataFile($directory), $file)->execute();

        $this->assertSame(
            'Tests that executed ' . $file . ' as it is now:' . PHP_EOL .
            ' - BarTest::testOne' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL . PHP_EOL,
            $result->output(),
        );

        $this->assertSame(Result::SUCCESS, $result->shellExitCode());
    }

    public function testListsTheTestsThatExecutedAnEarlierVersionOfTheSourceFileSeparately(): void
    {
        $directory = $this->temporaryDirectory();
        $file      = $this->writeSourceFile($directory, 'Foo', 'first');

        $first = new DefaultTestImpactData;
        $first->record('FooTest::testOne', [$file]);

        new TestImpactDataFile($directory)->persist($first);

        $this->writeSourceFile($directory, 'Foo', 'second');

        $second = new DefaultTestImpactData;
        $second->record('BarTest::testOne', [$file]);

        new TestImpactDataFile($directory)->persist($second);

        $result = new ListTestsThatExecutedCommand(new TestImpactDataFile($directory), $file)->execute();

        $this->assertSame(
            'Tests that executed ' . $file . ' as it is now:' . PHP_EOL .
            ' - BarTest::testOne' . PHP_EOL . PHP_EOL .
            'Tests that executed an earlier version of ' . $file . ':' . PHP_EOL .
            ' - FooTest::testOne' . PHP_EOL . PHP_EOL,
            $result->output(),
        );
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-list-tests-that-executed-' . uniqid();

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
