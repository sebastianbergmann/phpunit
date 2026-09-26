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
use function array_key_exists;
use function array_unique;
use function dirname;
use function getcwd;
use function hash;
use function implode;
use function is_array;
use function is_file;
use function is_string;
use function sort;
use PHPUnit\Runner\TestIndex\FileHasher;
use PHPUnit\TextUI\Configuration\Source;

/**
 * What everything that was recorded rests on.
 *
 * A test run answers what a test depends on for the settings of PHPUnit that
 * can change what code a test executes, for one bootstrap of the test suite,
 * for one idea of which code is first-party code, and for one set of installed
 * packages. When any of those is not what it was, what was recorded describes
 * a state of affairs that no longer exists, and the answer is not that some
 * entries are stale: it is that none of them can be relied on.
 *
 * Neither the settings nor the code that is first-party code are taken from
 * the configuration file: the command line changes both without the file
 * changing, and the file changes in ways that change neither.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Assumptions
{
    private const string COMPOSER_LOCK_FILENAME = 'composer.lock';

    /**
     * @var non-empty-string
     */
    private string $settings;

    /**
     * @var ?non-empty-string
     */
    private ?string $bootstrap;

    /**
     * @var non-empty-string
     */
    private string $source;

    /**
     * @var ?non-empty-string
     */
    private ?string $installedPackages;

    /**
     * The lock file of the package manager is looked for next to the
     * configuration file, and in the working directory when there is no
     * configuration file, and then in the directories above it: a
     * configuration file that is kept in a directory of its own is not next to
     * the lock file of the project it configures the tests of. That there is
     * no lock file is not the same as the lock file having changed: a project
     * that does not have one, or that is tested with a PHAR, is not a project
     * whose data has to be discarded.
     *
     * @param ?non-empty-string      $configurationFile the configuration file, which is only used to find the lock file
     * @param list<non-empty-string> $bootstrapFiles
     */
    public static function from(?string $configurationFile, ExecutionSettings $settings, Source $source, array $bootstrapFiles, ?FileHasher $hasher = null): self
    {
        if ($hasher === null) {
            $hasher = new FileHasher;
        }

        $lockFile = self::composerLockFileNearest($configurationFile);

        $installedPackages = null;

        if ($lockFile !== null) {
            $installedPackages = $hasher->hash($lockFile);
        }

        return new self(
            $settings->hash(),
            self::hashOfBootstrapFiles($bootstrapFiles, $hasher),
            self::hashOf($source),
            $installedPackages,
        );
    }

    /**
     * Returns null when what is there cannot be read.
     */
    public static function fromArray(mixed $data): ?self
    {
        if (!is_array($data) || !array_key_exists('settings', $data) || !array_key_exists('bootstrap', $data) || !array_key_exists('source', $data) || !array_key_exists('installedPackages', $data)) {
            return null;
        }

        $settings          = $data['settings'];
        $bootstrap         = $data['bootstrap'];
        $source            = $data['source'];
        $installedPackages = $data['installedPackages'];

        if (!is_string($settings) || $settings === '') {
            return null;
        }

        if ($bootstrap !== null && (!is_string($bootstrap) || $bootstrap === '')) {
            return null;
        }

        if (!is_string($source) || $source === '') {
            return null;
        }

        if ($installedPackages !== null && (!is_string($installedPackages) || $installedPackages === '')) {
            return null;
        }

        return new self($settings, $bootstrap, $source, $installedPackages);
    }

    /**
     * @param non-empty-string  $settings
     * @param ?non-empty-string $bootstrap
     * @param non-empty-string  $source
     * @param ?non-empty-string $installedPackages
     */
    private function __construct(string $settings, ?string $bootstrap, string $source, ?string $installedPackages)
    {
        $this->settings          = $settings;
        $this->bootstrap         = $bootstrap;
        $this->source            = $source;
        $this->installedPackages = $installedPackages;
    }

    /**
     * @return array{settings: non-empty-string, bootstrap: ?non-empty-string, source: non-empty-string, installedPackages: ?non-empty-string}
     */
    public function asArray(): array
    {
        return [
            'settings'          => $this->settings,
            'bootstrap'         => $this->bootstrap,
            'source'            => $this->source,
            'installedPackages' => $this->installedPackages,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->whatChangedSince($other) === null;
    }

    /**
     * What is not what it was when the other assumptions were made, or null
     * when nothing is. When more than one of them changed, the first one is
     * named: any one of them is reason enough to discard what was recorded.
     */
    public function whatChangedSince(self $other): ?DiscardReason
    {
        if ($this->settings !== $other->settings) {
            return DiscardReason::ConfigurationChanged;
        }

        if ($this->bootstrap !== $other->bootstrap) {
            return DiscardReason::BootstrapScriptChanged;
        }

        if ($this->source !== $other->source) {
            return DiscardReason::FirstPartyCodeChanged;
        }

        if ($this->installedPackages !== $other->installedPackages) {
            return DiscardReason::InstalledPackagesChanged;
        }

        return null;
    }

    /**
     * A bootstrap script registers autoloaders, defines constants, and sets up
     * global state: it can change what every test does without any of the
     * files a test executed changing, and it is not code that is subject to
     * code coverage analysis, so nothing else notices when it changes. That
     * is why the contents of the scripts are hashed here, and not which
     * scripts they are.
     *
     * The order the scripts are named in does not matter: which test suite a
     * script is used for is one of the execution settings, and those are part
     * of the assumptions as well.
     *
     * A script that cannot be read is passed over: a run whose bootstrap
     * script is not there does not get as far as recording anything.
     *
     * @param list<non-empty-string> $files
     *
     * @return ?non-empty-string
     */
    private static function hashOfBootstrapFiles(array $files, FileHasher $hasher): ?string
    {
        $hashes = [];

        foreach ($files as $file) {
            $hash = $hasher->hash($file);

            if ($hash === null) {
                continue;
            }

            $hashes[] = $hash;
        }

        if ($hashes === []) {
            return null;
        }

        $hashes = array_unique($hashes);

        sort($hashes);

        return hash('xxh128', implode("\n", $hashes));
    }

    /**
     * What makes a file first-party code, and not which files that happens to
     * be right now: a source file that is added to a directory that is already
     * included does not make what was recorded for the tests that exist wrong,
     * whereas including another directory does.
     *
     * @return non-empty-string
     */
    private static function hashOf(Source $source): string
    {
        $description = [];

        foreach ($source->includeDirectories() as $directory) {
            $description[] = 'include-directory ' . $directory->path() . ' ' . $directory->prefix() . ' ' . $directory->suffix();
        }

        foreach ($source->includeFiles() as $file) {
            $description[] = 'include-file ' . $file->path();
        }

        foreach ($source->excludeDirectories() as $directory) {
            $description[] = 'exclude-directory ' . $directory->path() . ' ' . $directory->prefix() . ' ' . $directory->suffix();
        }

        foreach ($source->excludeFiles() as $file) {
            $description[] = 'exclude-file ' . $file->path();
        }

        $description = array_unique($description);

        sort($description);

        return hash('xxh128', implode("\n", $description));
    }

    /**
     * The nearest lock file is the one that answers for the packages the tests
     * are run with: one that is further up is the lock file of something the
     * project is itself a part of, and taking it would only mean that what was
     * recorded is discarded when it does not have to be, which is the safe way
     * to be wrong.
     *
     * @param ?non-empty-string $configurationFile
     *
     * @return ?non-empty-string
     */
    private static function composerLockFileNearest(?string $configurationFile): ?string
    {
        if ($configurationFile !== null) {
            $directory = dirname($configurationFile);
        } else {
            $directory = getcwd();

            if ($directory === false) {
                return null; // @codeCoverageIgnore
            }
        }

        while (true) {
            $candidate = $directory . DIRECTORY_SEPARATOR . self::COMPOSER_LOCK_FILENAME;

            if (is_file($candidate)) {
                return $candidate;
            }

            $parent = dirname($directory);

            if ($parent === $directory) {
                return null;
            }

            $directory = $parent;
        }
    }
}
