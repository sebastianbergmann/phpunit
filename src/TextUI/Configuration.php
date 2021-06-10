<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use const DIRECTORY_SEPARATOR;
use function assert;
use function dirname;
use function is_dir;
use function is_file;
use function is_readable;
use function realpath;
use function substr;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\FilterMapper;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use PHPUnit\Util\Filesystem;
use SebastianBergmann\CodeCoverage\Filter as CodeCoverageFilter;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use Throwable;

/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Configuration
{
    private static ?self $instance = null;

    private ?TestSuite $testSuite;

    private ?string $bootstrap;

    private bool $cacheResult;

    private ?string $cacheDirectory;

    private ?string $coverageCacheDirectory;

    private string $testResultCacheFile;

    private CodeCoverageFilter $codeCoverageFilter;

    private bool $ignoreDeprecatedCodeUnitsFromCodeCoverage;

    private bool $disableCodeCoverageIgnore;

    private bool $failOnEmptyTestSuite;

    private bool $failOnIncomplete;

    private bool $failOnRisky;

    private bool $failOnSkipped;

    private bool $failOnWarning;

    public static function get(): self
    {
        assert(self::$instance instanceof self);

        return self::$instance;
    }

    public static function initFromCli(CliConfiguration $cliConfiguration): void
    {
        $bootstrap = null;

        if ($cliConfiguration->hasBootstrap()) {
            $bootstrap = $cliConfiguration->bootstrap();
        }

        if ($bootstrap !== null) {
            self::handleBootstrap($bootstrap);
        }

        $testSuite = null;

        if ($cliConfiguration->hasArgument()) {
            $argument = realpath($cliConfiguration->argument());

            if (!$argument) {
                throw new TestFileNotFoundException($cliConfiguration->argument());
            }

            $testSuite = self::testSuiteFromPath(
                $argument,
                self::testSuffixes($cliConfiguration)
            );
        }

        if ($cliConfiguration->hasCacheResult()) {
            $cacheResult = $cliConfiguration->cacheResult();
        } else {
            $cacheResult = true;
        }

        $cacheDirectory         = null;
        $coverageCacheDirectory = null;

        if ($cliConfiguration->hasCacheDirectory() && Filesystem::createDirectory($cliConfiguration->cacheDirectory())) {
            $cacheDirectory         = realpath($cliConfiguration->cacheDirectory());
            $coverageCacheDirectory = $cacheDirectory . DIRECTORY_SEPARATOR . 'code-coverage';
            $testResultCacheFile    = $cacheDirectory . DIRECTORY_SEPARATOR . 'test-results';
        } elseif ($cliConfiguration->hasCoverageCacheDirectory() && Filesystem::createDirectory($cliConfiguration->coverageCacheDirectory())) {
            $coverageCacheDirectory = realpath($cliConfiguration->coverageCacheDirectory());
        }

        if (!isset($testResultCacheFile)) {
            if ($cliConfiguration->hasCacheResultFile()) {
                $testResultCacheFile = $cliConfiguration->cacheResultFile();
            } else {
                $candidate = realpath($_SERVER['PHP_SELF']);

                if ($candidate) {
                    $testResultCacheFile = dirname($candidate) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
                } else {
                    $testResultCacheFile = '.phpunit.result.cache';
                }
            }
        }

        $codeCoverageFilter = new CodeCoverageFilter;

        if ($cliConfiguration->hasCoverageFilter()) {
            foreach ($cliConfiguration->coverageFilter() as $directory) {
                $codeCoverageFilter->includeDirectory($directory);
            }
        }

        if ($cliConfiguration->hasDisableCodeCoverageIgnore()) {
            $disableCodeCoverageIgnore = $cliConfiguration->disableCodeCoverageIgnore();
        } else {
            $disableCodeCoverageIgnore = false;
        }

        if ($cliConfiguration->hasFailOnEmptyTestSuite()) {
            $failOnEmptyTestSuite = $cliConfiguration->failOnEmptyTestSuite();
        } else {
            $failOnEmptyTestSuite = false;
        }

        if ($cliConfiguration->hasFailOnIncomplete()) {
            $failOnIncomplete = $cliConfiguration->failOnIncomplete();
        } else {
            $failOnIncomplete = false;
        }

        if ($cliConfiguration->hasFailOnRisky()) {
            $failOnRisky = $cliConfiguration->failOnRisky();
        } else {
            $failOnRisky = false;
        }

        if ($cliConfiguration->hasFailOnSkipped()) {
            $failOnSkipped = $cliConfiguration->failOnSkipped();
        } else {
            $failOnSkipped = false;
        }

        if ($cliConfiguration->hasFailOnWarning()) {
            $failOnWarning = $cliConfiguration->failOnWarning();
        } else {
            $failOnWarning = false;
        }

        self::$instance = new self(
            $testSuite,
            $bootstrap,
            $cacheResult,
            $cacheDirectory,
            $coverageCacheDirectory,
            $testResultCacheFile,
            $codeCoverageFilter,
            false,
            $disableCodeCoverageIgnore,
            $failOnEmptyTestSuite,
            $failOnIncomplete,
            $failOnRisky,
            $failOnSkipped,
            $failOnWarning
        );
    }

    /**
     * @throws TestFileNotFoundException
     */
    public static function initFromCliAndXml(CliConfiguration $cliConfiguration, XmlConfiguration $xmlConfiguration): void
    {
        $bootstrap = null;

        if ($cliConfiguration->hasBootstrap()) {
            $bootstrap = $cliConfiguration->bootstrap();
        } elseif ($xmlConfiguration->phpunit()->hasBootstrap()) {
            $bootstrap = $xmlConfiguration->phpunit()->bootstrap();
        }

        if ($bootstrap !== null) {
            self::handleBootstrap($bootstrap);
        }

        if ($cliConfiguration->hasArgument()) {
            $argument = realpath($cliConfiguration->argument());

            if (!$argument) {
                throw new TestFileNotFoundException($cliConfiguration->argument());
            }

            $testSuite = self::testSuiteFromPath(
                $argument,
                self::testSuffixes($cliConfiguration)
            );
        } else {
            $includeTestSuite = '';

            if ($cliConfiguration->hasTestSuite()) {
                $includeTestSuite = $cliConfiguration->testSuite();
            } elseif ($xmlConfiguration->phpunit()->hasDefaultTestSuite()) {
                $includeTestSuite = $xmlConfiguration->phpunit()->defaultTestSuite();
            }

            $testSuite = (new TestSuiteMapper)->map(
                $xmlConfiguration->testSuite(),
                $includeTestSuite,
                $cliConfiguration->hasExcludedTestSuite() ? $cliConfiguration->excludedTestSuite() : ''
            );
        }

        if ($cliConfiguration->hasCacheResult()) {
            $cacheResult = $cliConfiguration->cacheResult();
        } else {
            $cacheResult = $xmlConfiguration->phpunit()->cacheResult();
        }

        $cacheDirectory         = null;
        $coverageCacheDirectory = null;

        if ($cliConfiguration->hasCacheDirectory() && Filesystem::createDirectory($cliConfiguration->cacheDirectory())) {
            $cacheDirectory = realpath($cliConfiguration->cacheDirectory());
        } elseif ($xmlConfiguration->phpunit()->hasCacheDirectory() && Filesystem::createDirectory($xmlConfiguration->phpunit()->cacheDirectory())) {
            $cacheDirectory = realpath($xmlConfiguration->phpunit()->cacheDirectory());
        }

        if ($cacheDirectory !== null) {
            $coverageCacheDirectory = $cacheDirectory . DIRECTORY_SEPARATOR . 'code-coverage';
            $testResultCacheFile    = $cacheDirectory . DIRECTORY_SEPARATOR . 'test-results';
        }

        if ($coverageCacheDirectory === null) {
            if ($cliConfiguration->hasCoverageCacheDirectory() && Filesystem::createDirectory($cliConfiguration->coverageCacheDirectory())) {
                $coverageCacheDirectory = realpath($cliConfiguration->coverageCacheDirectory());
            } elseif ($xmlConfiguration->codeCoverage()->hasCacheDirectory()) {
                $coverageCacheDirectory = $xmlConfiguration->codeCoverage()->cacheDirectory()->path();
            }
        }

        if (!isset($testResultCacheFile)) {
            if ($cliConfiguration->hasCacheResultFile()) {
                $testResultCacheFile = $cliConfiguration->cacheResultFile();
            } elseif ($xmlConfiguration->phpunit()->hasCacheResultFile()) {
                $testResultCacheFile = $xmlConfiguration->phpunit()->cacheResultFile();
            } else {
                $testResultCacheFile = dirname(realpath($xmlConfiguration->filename())) . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
            }
        }

        $codeCoverageFilter = new CodeCoverageFilter;

        if ($cliConfiguration->hasCoverageFilter()) {
            foreach ($cliConfiguration->coverageFilter() as $directory) {
                $codeCoverageFilter->includeDirectory($directory);
            }
        }

        if ($xmlConfiguration->codeCoverage()->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
            (new FilterMapper)->map(
                $codeCoverageFilter,
                $xmlConfiguration->codeCoverage()
            );
        }

        if ($cliConfiguration->hasDisableCodeCoverageIgnore()) {
            $disableCodeCoverageIgnore = $cliConfiguration->disableCodeCoverageIgnore();
        } else {
            $disableCodeCoverageIgnore = $xmlConfiguration->codeCoverage()->disableCodeCoverageIgnore();
        }

        if ($cliConfiguration->hasFailOnEmptyTestSuite()) {
            $failOnEmptyTestSuite = $cliConfiguration->failOnEmptyTestSuite();
        } else {
            $failOnEmptyTestSuite = $xmlConfiguration->phpunit()->failOnEmptyTestSuite();
        }

        if ($cliConfiguration->hasFailOnIncomplete()) {
            $failOnIncomplete = $cliConfiguration->failOnIncomplete();
        } else {
            $failOnIncomplete = $xmlConfiguration->phpunit()->failOnIncomplete();
        }

        if ($cliConfiguration->hasFailOnRisky()) {
            $failOnRisky = $cliConfiguration->failOnRisky();
        } else {
            $failOnRisky = $xmlConfiguration->phpunit()->failOnRisky();
        }

        if ($cliConfiguration->hasFailOnSkipped()) {
            $failOnSkipped = $cliConfiguration->failOnSkipped();
        } else {
            $failOnSkipped = $xmlConfiguration->phpunit()->failOnSkipped();
        }

        if ($cliConfiguration->hasFailOnWarning()) {
            $failOnWarning = $cliConfiguration->failOnWarning();
        } else {
            $failOnWarning = $xmlConfiguration->phpunit()->failOnWarning();
        }

        self::$instance = new self(
            $testSuite,
            $bootstrap,
            $cacheResult,
            $cacheDirectory,
            $coverageCacheDirectory,
            $testResultCacheFile,
            $codeCoverageFilter,
            $xmlConfiguration->codeCoverage()->ignoreDeprecatedCodeUnits(),
            $disableCodeCoverageIgnore,
            $failOnEmptyTestSuite,
            $failOnIncomplete,
            $failOnRisky,
            $failOnSkipped,
            $failOnWarning
        );
    }

    private function __construct(?TestSuite $testSuite, ?string $bootstrap, bool $cacheResult, ?string $cacheDirectory, ?string $coverageCacheDirectory, string $testResultCacheFile, CodeCoverageFilter $codeCoverageFilter, bool $ignoreDeprecatedCodeUnitsFromCodeCoverage, bool $disableCodeCoverageIgnore, bool $failOnEmptyTestSuite, bool $failOnIncomplete, bool $failOnRisky, bool $failOnSkipped, bool $failOnWarning)
    {
        $this->testSuite                                 = $testSuite;
        $this->bootstrap                                 = $bootstrap;
        $this->cacheResult                               = $cacheResult;
        $this->cacheDirectory                            = $cacheDirectory;
        $this->coverageCacheDirectory                    = $coverageCacheDirectory;
        $this->testResultCacheFile                       = $testResultCacheFile;
        $this->codeCoverageFilter                        = $codeCoverageFilter;
        $this->ignoreDeprecatedCodeUnitsFromCodeCoverage = $ignoreDeprecatedCodeUnitsFromCodeCoverage;
        $this->disableCodeCoverageIgnore                 = $disableCodeCoverageIgnore;
        $this->failOnEmptyTestSuite                      = $failOnEmptyTestSuite;
        $this->failOnIncomplete                          = $failOnIncomplete;
        $this->failOnRisky                               = $failOnRisky;
        $this->failOnSkipped                             = $failOnSkipped;
        $this->failOnWarning                             = $failOnWarning;
    }

    /**
     * @psalm-assert-if-true !null $this->testSuite
     */
    public function hasTestSuite(): bool
    {
        return $this->testSuite !== null;
    }

    /**
     * @throws NoTestSuiteException
     */
    public function testSuite(): TestSuite
    {
        if ($this->testSuite === null) {
            throw new NoTestSuiteException;
        }

        return $this->testSuite;
    }

    /**
     * @psalm-assert-if-true !null $this->bootstrap
     */
    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws NoBootstrapException
     */
    public function bootstrap(): string
    {
        if ($this->bootstrap === null) {
            throw new NoBootstrapException;
        }

        return $this->bootstrap;
    }

    public function cacheResult(): bool
    {
        return $this->cacheResult;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheDirectory
     */
    public function hasCacheDirectory(): bool
    {
        return $this->cacheDirectory !== null;
    }

    /**
     * @throws NoCacheDirectoryException
     */
    public function cacheDirectory(): string
    {
        if ($this->cacheDirectory === null) {
            throw new NoCacheDirectoryException;
        }

        return $this->cacheDirectory;
    }

    /**
     * @psalm-assert-if-true !null $this->coverageCacheDirectory
     */
    public function hasCoverageCacheDirectory(): bool
    {
        return $this->coverageCacheDirectory !== null;
    }

    /**
     * @throws NoCoverageCacheDirectoryException
     */
    public function coverageCacheDirectory(): string
    {
        if ($this->coverageCacheDirectory === null) {
            throw new NoCoverageCacheDirectoryException;
        }

        return $this->coverageCacheDirectory;
    }

    public function testResultCacheFile(): string
    {
        return $this->testResultCacheFile;
    }

    public function codeCoverageFilter(): CodeCoverageFilter
    {
        return $this->codeCoverageFilter;
    }

    public function ignoreDeprecatedCodeUnitsFromCodeCoverage(): bool
    {
        return $this->ignoreDeprecatedCodeUnitsFromCodeCoverage;
    }

    public function disableCodeCoverageIgnore(): bool
    {
        return $this->disableCodeCoverageIgnore;
    }

    public function failOnEmptyTestSuite(): bool
    {
        return $this->failOnEmptyTestSuite;
    }

    public function failOnIncomplete(): bool
    {
        return $this->failOnIncomplete;
    }

    public function failOnRisky(): bool
    {
        return $this->failOnRisky;
    }

    public function failOnSkipped(): bool
    {
        return $this->failOnSkipped;
    }

    public function failOnWarning(): bool
    {
        return $this->failOnWarning;
    }

    /**
     * @psalm-param list<string> $suffixes
     */
    private static function testSuiteFromPath(string $path, array $suffixes): TestSuite
    {
        if (is_dir($path)) {
            $files = (new FileIteratorFacade)->getFilesAsArray($path, $suffixes);

            $suite = new TestSuite($path);
            $suite->addTestFiles($files);

            return $suite;
        }

        if (is_file($path) && substr($path, -5, 5) === '.phpt') {
            $suite = new TestSuite;
            $suite->addTestFile($path);

            return $suite;
        }

        try {
            $testClass = (new TestSuiteLoader)->load($path);
        } catch (\PHPUnit\Exception $e) {
            print $e->getMessage() . PHP_EOL;

            exit(1);
        }

        return new TestSuite($testClass);
    }

    private static function testSuffixes(CliConfiguration $cliConfiguration): array
    {
        $testSuffixes = ['Test.php', '.phpt'];

        if ($cliConfiguration->hasTestSuffixes()) {
            $testSuffixes = $cliConfiguration->testSuffixes();
        }

        return $testSuffixes;
    }

    /**
     * @throws InvalidBootstrapException
     */
    private static function handleBootstrap(string $filename): void
    {
        if (!is_readable($filename)) {
            throw new InvalidBootstrapException($filename);
        }

        try {
            include $filename;
        } catch (Throwable $t) {
            throw new BootstrapException($t);
        }

        Facade::emitter()->bootstrapFinished($filename);
    }
}
