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
 * A test run answers what a test depends on for one configuration of PHPUnit,
 * of the code that is first-party code, and of the packages that are
 * installed. When any of those is not what it was, what was recorded describes
 * a state of affairs that no longer exists, and the answer is not that some
 * entries are stale: it is that none of them can be relied on.
 *
 * The code that is first-party code is not taken from the configuration file
 * alone: --coverage-filter widens it from the command line without the file
 * changing.
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
     * @var ?non-empty-string
     */
    private ?string $configuration;

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
     * configuration file. That there is no lock file is not the same as the
     * lock file having changed: a project that does not have one, or that is
     * tested with a PHAR, is not a project whose data has to be discarded.
     *
     * @param ?non-empty-string $configurationFile
     */
    public static function from(?string $configurationFile, Source $source, ?FileHasher $hasher = null): self
    {
        if ($hasher === null) {
            $hasher = new FileHasher;
        }

        $configuration = null;

        if ($configurationFile !== null) {
            $configuration = $hasher->hash($configurationFile);
        }

        $lockFile = self::composerLockFileNextTo($configurationFile);

        $installedPackages = null;

        if ($lockFile !== null) {
            $installedPackages = $hasher->hash($lockFile);
        }

        return new self($configuration, self::hashOf($source), $installedPackages);
    }

    /**
     * Returns null when what is there cannot be read.
     */
    public static function fromArray(mixed $data): ?self
    {
        if (!is_array($data) || !array_key_exists('configuration', $data) || !array_key_exists('source', $data) || !array_key_exists('installedPackages', $data)) {
            return null;
        }

        $configuration     = $data['configuration'];
        $source            = $data['source'];
        $installedPackages = $data['installedPackages'];

        if ($configuration !== null && (!is_string($configuration) || $configuration === '')) {
            return null;
        }

        if (!is_string($source) || $source === '') {
            return null;
        }

        if ($installedPackages !== null && (!is_string($installedPackages) || $installedPackages === '')) {
            return null;
        }

        return new self($configuration, $source, $installedPackages);
    }

    /**
     * @param ?non-empty-string $configuration
     * @param non-empty-string  $source
     * @param ?non-empty-string $installedPackages
     */
    private function __construct(?string $configuration, string $source, ?string $installedPackages)
    {
        $this->configuration     = $configuration;
        $this->source            = $source;
        $this->installedPackages = $installedPackages;
    }

    /**
     * @return array{configuration: ?non-empty-string, source: non-empty-string, installedPackages: ?non-empty-string}
     */
    public function asArray(): array
    {
        return [
            'configuration'     => $this->configuration,
            'source'            => $this->source,
            'installedPackages' => $this->installedPackages,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->configuration === $other->configuration &&
               $this->source === $other->source &&
               $this->installedPackages === $other->installedPackages;
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
     * @param ?non-empty-string $configurationFile
     *
     * @return ?non-empty-string
     */
    private static function composerLockFileNextTo(?string $configurationFile): ?string
    {
        if ($configurationFile !== null) {
            $directory = dirname($configurationFile);
        } else {
            $directory = getcwd();

            if ($directory === false) {
                return null; // @codeCoverageIgnore
            }
        }

        $candidate = $directory . DIRECTORY_SEPARATOR . self::COMPOSER_LOCK_FILENAME;

        if (!is_file($candidate)) {
            return null;
        }

        return $candidate;
    }
}
