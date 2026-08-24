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
use PHPUnit\Runner\TestIndex\FileHasher;
use PHPUnit\TextUI\Configuration\FilterDirectory;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFile;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;

#[CoversClass(Assumptions::class)]
#[UsesClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class AssumptionsTest extends TestCase
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

    public function testAreTheSameWhenNothingChanged(): void
    {
        $this->assertTrue(
            Assumptions::from(null, $this->source())->equals(Assumptions::from(null, $this->source())),
        );
    }

    public function testAreNotTheSameWhenTheConfigurationFileChanged(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $before = Assumptions::from($configurationFile, $this->source());

        $this->writeFile($directory, 'phpunit.xml', 'second');

        $this->assertFalse($before->equals(Assumptions::from($configurationFile, $this->source())));
    }

    public function testAreNotTheSameWhenThereIsNoConfigurationFileAnyLonger(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $this->assertFalse(
            Assumptions::from($configurationFile, $this->source())->equals(Assumptions::from(null, $this->source())),
        );
    }

    public function testAreNotTheSameWhenAnotherDirectoryIsFirstPartyCode(): void
    {
        $this->assertFalse(
            Assumptions::from(null, $this->source('src'))->equals(Assumptions::from(null, $this->source('lib'))),
        );
    }

    public function testAreNotTheSameWhenAFileIsNoLongerFirstPartyCode(): void
    {
        $before = Assumptions::from(null, $this->source('src'));
        $after  = Assumptions::from(null, $this->source('src', 'src/Excluded.php'));

        $this->assertFalse($before->equals($after));
    }

    public function testAreTheSameWhenTheSameDirectoriesAreFirstPartyCodeInAnotherOrder(): void
    {
        $this->assertTrue(
            Assumptions::from(null, $this->source('src', null, ['a', 'b']))->equals(
                Assumptions::from(null, $this->source('src', null, ['b', 'a'])),
            ),
        );
    }

    public function testAreTheSameWhenADirectoryThatIsAlreadyFirstPartyCodeIsNamedAgain(): void
    {
        $this->assertTrue(
            Assumptions::from(null, $this->source('src'))->equals(
                Assumptions::from(null, $this->source('src', null, ['src'])),
            ),
        );
    }

    public function testAreNotTheSameWhenTheLockFileOfThePackageManagerChanged(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $this->writeFile($directory, 'composer.lock', 'first');

        $before = Assumptions::from($configurationFile, $this->source());

        $this->writeFile($directory, 'composer.lock', 'second');

        $this->assertFalse($before->equals(Assumptions::from($configurationFile, $this->source())));
    }

    public function testAreNotTheSameWhenThereIsALockFileWhereThereWasNone(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $before = Assumptions::from($configurationFile, $this->source());

        $this->writeFile($directory, 'composer.lock', 'first');

        $this->assertFalse($before->equals(Assumptions::from($configurationFile, $this->source())));
    }

    public function testSurviveBeingWrittenAndReadAgain(): void
    {
        $assumptions = Assumptions::from(null, $this->source('src'));

        $this->assertTrue($assumptions->equals(Assumptions::fromArray($assumptions->asArray())));
    }

    public function testCannotBeReadFromSomethingThatIsNotAnArray(): void
    {
        $this->assertNull(Assumptions::fromArray('guesswork'));
    }

    public function testCannotBeReadFromAnArrayThatIsIncomplete(): void
    {
        $this->assertNull(Assumptions::fromArray(['source' => 'a-hash']));
    }

    public function testCannotBeReadWhenAValueIsNotUsable(): void
    {
        $this->assertNull(Assumptions::fromArray(['configuration' => 1, 'source' => 'a-hash', 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['configuration' => null, 'source' => '', 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['configuration' => null, 'source' => 'a-hash', 'installedPackages' => 1]));
    }

    /**
     * @param list<non-empty-string> $additionalDirectories
     */
    private function source(?string $includeDirectory = null, ?string $excludeFile = null, array $additionalDirectories = []): Source
    {
        $includeDirectories = [];

        if ($includeDirectory !== null) {
            $includeDirectories[] = new FilterDirectory($includeDirectory, '', '.php');
        }

        foreach ($additionalDirectories as $directory) {
            $includeDirectories[] = new FilterDirectory($directory, '', '.php');
        }

        $excludeFiles = [];

        if ($excludeFile !== null) {
            $excludeFiles[] = new FilterFile($excludeFile);
        }

        return new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray($includeDirectories),
            FilterFileCollection::fromArray([new FilterFile('src/Included.php')]),
            FilterDirectoryCollection::fromArray([new FilterDirectory('src/excluded', '', '.php')]),
            FilterFileCollection::fromArray($excludeFiles),
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
        );
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-assumptions-' . uniqid();

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
    private function writeFile(string $directory, string $name, string $contents): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name;

        file_put_contents($file, $contents);

        return $file;
    }
}
