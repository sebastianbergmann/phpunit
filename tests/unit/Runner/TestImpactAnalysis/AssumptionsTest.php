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
use function dirname;
use function file_put_contents;
use function is_dir;
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
use PHPUnit\TextUI\Configuration\AddedFilesAreReachedThroughChanges;
use PHPUnit\TextUI\Configuration\FilterDirectory;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;
use PHPUnit\TextUI\Configuration\FilterFile;
use PHPUnit\TextUI\Configuration\FilterFileCollection;
use PHPUnit\TextUI\Configuration\Source;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(Assumptions::class)]
#[UsesClass(BaseDirectory::class)]
#[UsesClass(ExecutionSettings::class)]
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
            $this->deleteDirectory($directory);
        }

        $this->directories = [];
    }

    public function testAreTheSameWhenNothingChanged(): void
    {
        $this->assertTrue(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])->equals(Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])),
        );
    }

    public function testAreTheSameWhenOnlyTheConfigurationFileChanged(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $before = Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), []);

        $this->writeFile($directory, 'phpunit.xml', 'second');

        $this->assertTrue($before->equals(Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])));
    }

    public function testAreNotTheSameWhenTheExecutionSettingsChanged(): void
    {
        $this->assertFalse(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(true), $this->source(), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    public function testAreNotTheSameWhenTheBootstrapScriptChanged(): void
    {
        $directory = $this->temporaryDirectory();
        $bootstrap = $this->writeFile($directory, 'bootstrap.php', 'first');

        $before = Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$bootstrap]);

        $this->writeFile($directory, 'bootstrap.php', 'second');

        $this->assertFalse($before->equals(Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$bootstrap])));
    }

    public function testAreNotTheSameWhenThereIsNoBootstrapScriptAnyLonger(): void
    {
        $directory = $this->temporaryDirectory();
        $bootstrap = $this->writeFile($directory, 'bootstrap.php', 'first');

        $this->assertFalse(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$bootstrap])->equals(Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])),
        );
    }

    public function testAreNotTheSameWhenAnotherBootstrapScriptIsUsedForATestSuite(): void
    {
        $directory = $this->temporaryDirectory();
        $bootstrap = $this->writeFile($directory, 'bootstrap.php', 'first');
        $forSuite  = $this->writeFile($directory, 'bootstrap-for-suite.php', 'second');

        $this->assertFalse(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$bootstrap])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$bootstrap, $forSuite]),
            ),
        );
    }

    public function testAreTheSameWhenTheBootstrapScriptsAreNamedInAnotherOrder(): void
    {
        $directory = $this->temporaryDirectory();
        $first     = $this->writeFile($directory, 'first.php', 'first');
        $second    = $this->writeFile($directory, 'second.php', 'second');

        $this->assertTrue(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$first, $second])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$second, $first]),
            ),
        );
    }

    public function testAreTheSameWhenABootstrapScriptThatIsNotThereIsNamed(): void
    {
        $this->assertTrue(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [__DIR__ . '/does-not-exist.php'])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    public function testAreNotTheSameWhenAnotherDirectoryIsFirstPartyCode(): void
    {
        $this->assertFalse(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges(), [])->equals(Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('lib'), $this->addedFilesAreReachedThroughChanges(), [])),
        );
    }

    public function testAreNotTheSameWhenAFileIsNoLongerFirstPartyCode(): void
    {
        $before = Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges(), []);
        $after  = Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src', 'src/Excluded.php'), $this->addedFilesAreReachedThroughChanges(), []);

        $this->assertFalse($before->equals($after));
    }

    public function testAreNotTheSameWhenAddedFilesAreReachedThroughChangesInAnotherDirectory(): void
    {
        $this->assertFalse(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges('src'), [])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges('src/Services'), []),
            ),
        );
    }

    public function testAreNotTheSameWhenAddedFilesAreReachedThroughChangesWhereTheyWereNotBefore(): void
    {
        $this->assertFalse(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges(), [])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges('src'), []),
            ),
        );
    }

    public function testAreTheSameWhenAddedFilesAreReachedThroughChangesInTheSameDirectoryOfAnotherCheckout(): void
    {
        $this->assertTrue(
            Assumptions::from(BaseDirectory::from('/checkout'), $this->settings(), $this->sourceIn('/checkout'), $this->addedFilesAreReachedThroughChanges('/checkout/src'), [])->equals(
                Assumptions::from(BaseDirectory::from('/another/checkout'), $this->settings(), $this->sourceIn('/another/checkout'), $this->addedFilesAreReachedThroughChanges('/another/checkout/src'), []),
            ),
        );
    }

    public function testAreTheSameWhenTheSameDirectoriesAreFirstPartyCodeInAnotherOrder(): void
    {
        $this->assertTrue(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src', null, ['a', 'b']), $this->addedFilesAreReachedThroughChanges(), [])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src', null, ['b', 'a']), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    /**
     * The same directories in another checkout of the project, the checkout
     * of another CI runner for instance, are the same first-party code.
     */
    public function testAreTheSameWhenTheSameFirstPartyCodeIsInAnotherCheckout(): void
    {
        $this->assertTrue(
            Assumptions::from(BaseDirectory::from('/checkout'), $this->settings(), $this->sourceIn('/checkout'), $this->addedFilesAreReachedThroughChanges(), [])->equals(
                Assumptions::from(BaseDirectory::from('/another/checkout'), $this->settings(), $this->sourceIn('/another/checkout'), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    public function testAreTheSameWhenADirectoryThatIsAlreadyFirstPartyCodeIsNamedAgain(): void
    {
        $this->assertTrue(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges(), [])->equals(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src', null, ['src']), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    public function testAreNotTheSameWhenTheLockFileOfThePackageManagerChanged(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $this->writeFile($directory, 'composer.lock', 'first');

        $before = Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), []);

        $this->writeFile($directory, 'composer.lock', 'second');

        $this->assertFalse($before->equals(Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])));
    }

    public function testAreNotTheSameWhenThereIsALockFileWhereThereWasNone(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $before = Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), []);

        $this->writeFile($directory, 'composer.lock', 'first');

        $this->assertFalse($before->equals(Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])));
    }

    public function testAreNotTheSameWhenTheLockFileOfThePackageManagerAboveTheConfigurationFileChanged(): void
    {
        $directory = $this->temporaryDirectory();
        $build     = $directory . DIRECTORY_SEPARATOR . 'build';

        mkdir($build);

        $configurationFile = $this->writeFile($build, 'phpunit.xml', 'first');

        $this->writeFile($directory, 'composer.lock', 'first');

        $before = Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), []);

        $this->writeFile($directory, 'composer.lock', 'second');

        $this->assertFalse($before->equals(Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])));
    }

    public function testNameNothingThatChangedWhenNothingChanged(): void
    {
        $this->assertNull(
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])->whatChangedSince(Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])),
        );
    }

    public function testNameTheConfigurationWhenTheExecutionSettingsChanged(): void
    {
        $this->assertSame(
            DiscardReason::ConfigurationChanged,
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(true), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])->whatChangedSince(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    public function testNameTheBootstrapScriptWhenItChanged(): void
    {
        $directory = $this->temporaryDirectory();
        $bootstrap = $this->writeFile($directory, 'bootstrap.php', 'first');

        $before = Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$bootstrap]);

        $this->writeFile($directory, 'bootstrap.php', 'second');

        $this->assertSame(
            DiscardReason::BootstrapScriptChanged,
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [$bootstrap])->whatChangedSince($before),
        );
    }

    public function testNameWhatIsFirstPartyCodeWhenItChanged(): void
    {
        $this->assertSame(
            DiscardReason::FirstPartyCodeChanged,
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('lib'), $this->addedFilesAreReachedThroughChanges(), [])->whatChangedSince(Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges(), [])),
        );
    }

    public function testNameWhereAddedFilesAreReachedThroughChangesWhenItChanged(): void
    {
        $this->assertSame(
            DiscardReason::WhereAddedFilesAreReachedThroughChangesChanged,
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges('src'), [])->whatChangedSince(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    public function testNameTheLockFileOfThePackageManagerWhenItChanged(): void
    {
        $directory         = $this->temporaryDirectory();
        $configurationFile = $this->writeFile($directory, 'phpunit.xml', 'first');

        $this->writeFile($directory, 'composer.lock', 'first');

        $before = Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), []);

        $this->writeFile($directory, 'composer.lock', 'second');

        $this->assertSame(
            DiscardReason::InstalledPackagesChanged,
            Assumptions::from(BaseDirectory::from(dirname($configurationFile)), $this->settings(), $this->source(), $this->addedFilesAreReachedThroughChanges(), [])->whatChangedSince($before),
        );
    }

    public function testNameTheConfigurationWhenMoreThanOneThingChanged(): void
    {
        $this->assertSame(
            DiscardReason::ConfigurationChanged,
            Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(true), $this->source('lib'), $this->addedFilesAreReachedThroughChanges(), [])->whatChangedSince(
                Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges(), []),
            ),
        );
    }

    public function testSurviveBeingWrittenAndReadAgain(): void
    {
        $assumptions = Assumptions::from(BaseDirectory::fromWorkingDirectory(), $this->settings(), $this->source('src'), $this->addedFilesAreReachedThroughChanges('src'), []);

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
        $this->assertNull(Assumptions::fromArray(['settings' => null, 'bootstrap' => null, 'source' => 'a-hash', 'addedFilesAreReachedThroughChanges' => 'a-hash', 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['settings' => '', 'bootstrap' => null, 'source' => 'a-hash', 'addedFilesAreReachedThroughChanges' => 'a-hash', 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['settings' => 'a-hash', 'bootstrap' => 1, 'source' => 'a-hash', 'addedFilesAreReachedThroughChanges' => 'a-hash', 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['settings' => 'a-hash', 'bootstrap' => null, 'source' => '', 'addedFilesAreReachedThroughChanges' => 'a-hash', 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['settings' => 'a-hash', 'bootstrap' => null, 'source' => 'a-hash', 'addedFilesAreReachedThroughChanges' => '', 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['settings' => 'a-hash', 'bootstrap' => null, 'source' => 'a-hash', 'addedFilesAreReachedThroughChanges' => null, 'installedPackages' => null]));
        $this->assertNull(Assumptions::fromArray(['settings' => 'a-hash', 'bootstrap' => null, 'source' => 'a-hash', 'addedFilesAreReachedThroughChanges' => 'a-hash', 'installedPackages' => 1]));
    }

    private function settings(bool $processIsolation = false): ExecutionSettings
    {
        return ExecutionSettings::from(BaseDirectory::fromWorkingDirectory(), DefaultConfiguration::create()->php(), [], [], false, $processIsolation, false, false);
    }

    private function addedFilesAreReachedThroughChanges(?string $includeDirectory = null): AddedFilesAreReachedThroughChanges
    {
        $includeDirectories = [];

        if ($includeDirectory !== null) {
            $includeDirectories[] = new FilterDirectory($includeDirectory, '', '.php');
        }

        return new AddedFilesAreReachedThroughChanges(
            FilterDirectoryCollection::fromArray($includeDirectories),
            FilterFileCollection::fromArray([]),
            FilterDirectoryCollection::fromArray([]),
            FilterFileCollection::fromArray([]),
        );
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
     * First-party code the way the configuration file of a project in the
     * checkout names it: by absolute paths, made from the directory the
     * configuration file is in.
     *
     * @param non-empty-string $checkout
     */
    private function sourceIn(string $checkout): Source
    {
        return new Source(
            null,
            false,
            FilterDirectoryCollection::fromArray([new FilterDirectory($checkout . '/src', '', '.php')]),
            FilterFileCollection::fromArray([new FilterFile($checkout . '/lib/functions.php')]),
            FilterDirectoryCollection::fromArray([new FilterDirectory($checkout . '/src/generated', '', '.php')]),
            FilterFileCollection::fromArray([new FilterFile($checkout . '/src/Excluded.php')]),
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
