<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use const DIRECTORY_SEPARATOR;
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestIndex\TestFileSkipper;
use PHPUnit\TextUI\CliArguments\Builder as CliConfigurationBuilder;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use RuntimeException;

#[CoversClass(TestSuiteBuilder::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
final class TestSuiteBuilderTest extends TestCase
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

    #[TestDox('Loads every test file when no test file skipper is used')]
    public function testLoadsEveryTestFileWhenNoTestFileSkipperIsUsed(): void
    {
        $directory = $this->directory();

        $this->writeTestClass($directory, 'Built');

        $testSuite = (new TestSuiteBuilder)->build($this->configurationFor($directory));

        $this->assertSame(1, $testSuite->count());
    }

    #[TestDox('Does not load a test file that cannot contribute a test to the run')]
    public function testDoesNotLoadTestFileThatCanBeSkipped(): void
    {
        $directory = $this->directory();

        $this->writeTestClass($directory, 'SkippedByBuilder');

        $skipper = $this->createMock(TestFileSkipper::class);

        $skipper
            ->expects($this->once())
            ->method('canSkipLoading')
            ->willReturn(true);

        $skipper
            ->expects($this->never())
            ->method('record');

        $skipper
            ->expects($this->once())
            ->method('persist')
            ->seal();

        $testSuite = new TestSuiteBuilder($skipper)->build($this->configurationFor($directory));

        $this->assertSame(0, $testSuite->count());
    }

    #[TestDox('Loads a test file through the test file skipper, so that a file that cannot be loaded is not remembered')]
    public function testLoadsTestFileThroughTestFileSkipper(): void
    {
        $directory = $this->directory();

        $this->writeTestFileThatCannotBeLoaded($directory, 'BrokenByBuilder');

        $skipper = $this->createMock(TestFileSkipper::class);

        $skipper
            ->expects($this->once())
            ->method('canSkipLoading')
            ->willReturn(false);

        $skipper
            ->expects($this->once())
            ->method('record')
            ->willReturnCallback(static fn (string $file, Closure $load): mixed => $load())
            ->seal();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('BrokenByBuilderTest.php cannot be loaded');

        new TestSuiteBuilder($skipper)->build($this->configurationFor($directory));
    }

    /**
     * @param non-empty-string $directory
     */
    private function configurationFor(string $directory): Configuration
    {
        return (new Merger)->merge(
            // the first parameter is the name of the script that was invoked
            (new CliConfigurationBuilder)->fromParameters(['phpunit', $directory]),
            DefaultConfiguration::create(),
        );
    }

    /**
     * @return non-empty-string
     */
    private function directory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-suite-builder-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestClass(string $directory, string $name): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestSuiteBuilder;

                use PHPUnit\Framework\Attributes\Small;
                use PHPUnit\Framework\TestCase;

                #[Small]
                final class {$name}Test extends TestCase
                {
                    public function testOne(): void
                    {
                    }
                }
                PHP,
        );

        return $file;
    }

    /**
     * A test file that throws while it is being loaded: loading a test file can
     * fail in a way that is reported instead of ending the run.
     *
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestFileThatCannotBeLoaded(string $directory, string $name): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                throw new \RuntimeException('{$name}Test.php cannot be loaded');
                PHP,
        );

        return $file;
    }
}
