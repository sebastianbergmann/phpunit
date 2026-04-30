<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\CliArguments;

use const DIRECTORY_SEPARATOR;
use function chdir;
use function file_put_contents;
use function getcwd;
use function mkdir;
use function realpath;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlConfigurationFileFinder::class)]
#[Medium]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/cli')]
final class XmlConfigurationFileFinderTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $filesToRemove = [];

    /**
     * @var list<string>
     */
    private array $directoriesToRemove        = [];
    private ?string $previousWorkingDirectory = null;

    protected function tearDown(): void
    {
        if ($this->previousWorkingDirectory !== null) {
            chdir($this->previousWorkingDirectory);

            $this->previousWorkingDirectory = null;
        }

        foreach ($this->filesToRemove as $file) {
            @unlink($file);
        }

        foreach ($this->directoriesToRemove as $directory) {
            @rmdir($directory);
        }

        $this->filesToRemove       = [];
        $this->directoriesToRemove = [];
    }

    public function testReturnsConfigurationFilePathWhenItIsARegularFile(): void
    {
        $directory = $this->createDirectory();
        $file      = $this->createFile($directory . DIRECTORY_SEPARATOR . 'custom-name.xml', '<phpunit/>');

        $configuration = (new Builder)->fromParameters(['--configuration', $file]);

        $this->assertSame($file, (new XmlConfigurationFileFinder)->find($configuration));
    }

    public function testReturnsPhpunitXmlInDirectoryGivenAsConfigurationFile(): void
    {
        $directory = $this->createDirectory();
        $file      = $this->createFile($directory . DIRECTORY_SEPARATOR . 'phpunit.xml', '<phpunit/>');

        $configuration = (new Builder)->fromParameters(['--configuration', $directory]);

        $this->assertSame(realpath($file), (new XmlConfigurationFileFinder)->find($configuration));
    }

    public function testReturnsPhpunitDistXmlInDirectoryGivenAsConfigurationFile(): void
    {
        $directory = $this->createDirectory();
        $file      = $this->createFile($directory . DIRECTORY_SEPARATOR . 'phpunit.dist.xml', '<phpunit/>');

        $configuration = (new Builder)->fromParameters(['--configuration', $directory]);

        $this->assertSame(realpath($file), (new XmlConfigurationFileFinder)->find($configuration));
    }

    public function testReturnsPhpunitXmlDistInDirectoryGivenAsConfigurationFile(): void
    {
        $directory = $this->createDirectory();
        $file      = $this->createFile($directory . DIRECTORY_SEPARATOR . 'phpunit.xml.dist', '<phpunit/>');

        $configuration = (new Builder)->fromParameters(['--configuration', $directory]);

        $this->assertSame(realpath($file), (new XmlConfigurationFileFinder)->find($configuration));
    }

    public function testReturnsFalseWhenDirectoryGivenAsConfigurationFileDoesNotContainAnyCandidate(): void
    {
        $directory = $this->createDirectory();

        $configuration = (new Builder)->fromParameters(['--configuration', $directory]);

        $this->assertFalse((new XmlConfigurationFileFinder)->find($configuration));
    }

    public function testReturnsFalseWhenNoConfigurationFileIsGivenAndUseDefaultConfigurationIsDisabled(): void
    {
        $configuration = (new Builder)->fromParameters(['--no-configuration']);

        $this->assertFalse((new XmlConfigurationFileFinder)->find($configuration));
    }

    public function testReturnsFalseWhenNoConfigurationFileIsGivenAndCurrentWorkingDirectoryDoesNotContainCandidate(): void
    {
        $directory = $this->createDirectory();

        $this->changeWorkingDirectoryTo($directory);

        $configuration = (new Builder)->fromParameters([]);

        $this->assertFalse((new XmlConfigurationFileFinder)->find($configuration));
    }

    public function testReturnsConfigurationFileFoundInCurrentWorkingDirectoryWhenNoneIsGiven(): void
    {
        $directory = $this->createDirectory();
        $file      = $this->createFile($directory . DIRECTORY_SEPARATOR . 'phpunit.xml', '<phpunit/>');

        $this->changeWorkingDirectoryTo($directory);

        $configuration = (new Builder)->fromParameters([]);

        $this->assertSame(realpath($file), (new XmlConfigurationFileFinder)->find($configuration));
    }

    private function createFile(string $path, string $contents): string
    {
        file_put_contents($path, $contents);

        $this->filesToRemove[] = $path;

        return $path;
    }

    private function createDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-cli-finder-' . uniqid('', true);

        mkdir($directory);

        $this->directoriesToRemove[] = $directory;

        return $directory;
    }

    private function changeWorkingDirectoryTo(string $directory): void
    {
        $cwd = getcwd();

        if ($cwd === false) {
            $this->fail('Failed to capture current working directory.');
        }

        $this->previousWorkingDirectory = $cwd;

        chdir($directory);
    }
}
