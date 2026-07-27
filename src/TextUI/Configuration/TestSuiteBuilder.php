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
use const PHP_EOL;
use function assert;
use function count;
use function dirname;
use function file;
use function is_dir;
use function is_file;
use function realpath;
use function str_ends_with;
use function trim;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestIndex\NullTestFileSkipper;
use PHPUnit\Runner\TestIndex\TestFileSkipper;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TextUI\RuntimeException;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;
use PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteBuilder
{
    private TestFileSkipper $skipper;

    public function __construct(?TestFileSkipper $skipper = null)
    {
        if ($skipper === null) {
            $skipper = new NullTestFileSkipper;
        }

        $this->skipper = $skipper;
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function build(Configuration $configuration): TestSuite
    {
        $numberOfRuns = $configuration->repeat();
        $maxAttempts  = $configuration->retry();

        if ($numberOfRuns > 1) {
            // the --repeat CLI option takes precedence over the --retry CLI option
            $maxAttempts = 1;
        }

        if ($configuration->hasCliArguments() || $configuration->hasTestFilesFile()) {
            $arguments = [];

            if ($configuration->hasCliArguments()) {
                foreach ($configuration->cliArguments() as $cliArgument) {
                    $argument = realpath($cliArgument);

                    if ($argument === false) {
                        throw new TestFileNotFoundException($cliArgument);
                    }

                    $arguments[] = $argument;
                }
            }

            if ($configuration->hasTestFilesFile()) {
                if (!is_file($configuration->testFilesFile())) {
                    throw new RuntimeException('Cannot read from ' . $configuration->testFilesFile());
                }

                $directory = dirname($configuration->testFilesFile()) . DIRECTORY_SEPARATOR;

                $fileLines = file($configuration->testFilesFile());

                // @codeCoverageIgnoreStart
                if ($fileLines === false) {
                    throw new RuntimeException('Cannot read from ' . $configuration->testFilesFile());
                }
                // @codeCoverageIgnoreEnd

                foreach ($fileLines as $file) {
                    $file     = trim($file);
                    $argument = realpath($file);

                    if ($argument === false) {
                        $argument = realpath($directory . $file);
                    }

                    if ($argument === false) {
                        throw new TestFileNotFoundException($file);
                    }

                    $arguments[] = $argument;
                }
            }

            if (count($arguments) === 1) {
                $testSuite = $this->testSuiteFromPath(
                    $arguments[0],
                    $configuration->testSuffixes(),
                    $numberOfRuns,
                    $maxAttempts,
                );
            } else {
                $testSuite = $this->testSuiteFromPathList(
                    $arguments,
                    $configuration->testSuffixes(),
                    $numberOfRuns,
                    $maxAttempts,
                );
            }
        }

        if (!isset($testSuite)) {
            $xmlConfigurationFile = $configuration->hasConfigurationFile() ? $configuration->configurationFile() : 'Root Test Suite';

            assert($xmlConfigurationFile !== '');

            $testSuite = new TestSuiteMapper($this->skipper)->map(
                $xmlConfigurationFile,
                $configuration->testSuite(),
                $configuration->ignoreTestSelectionInXmlConfiguration() ? [] : $configuration->includeTestSuites(),
                $configuration->ignoreTestSelectionInXmlConfiguration() ? [] : $configuration->excludeTestSuites(),
                $numberOfRuns,
                $maxAttempts,
            );
        }

        /*
         * The index is written once, after the test suite has been built, and
         * not while it is being built.
         */
        $this->skipper->persist();

        EventFacade::emitter()->testSuiteLoaded(\PHPUnit\Event\TestSuite\TestSuiteBuilder::from($testSuite));

        return $testSuite;
    }

    /**
     * @param non-empty-string       $path
     * @param list<non-empty-string> $suffixes
     * @param positive-int           $numberOfRuns
     * @param positive-int           $maxAttempts
     *
     * @throws \PHPUnit\Framework\Exception
     */
    private function testSuiteFromPath(string $path, array $suffixes, int $numberOfRuns, int $maxAttempts, ?TestSuite $suite = null): TestSuite
    {
        if (str_ends_with($path, '.phpt') && is_file($path)) {
            if ($suite === null) {
                $suite = TestSuite::empty($path);
            }

            $suite->addTestFile($path, [], $numberOfRuns, $maxAttempts);

            return $suite;
        }

        if (is_dir($path)) {
            $files = (new FileIteratorFacade)->getFilesAsArray($path, $suffixes);

            if ($suite === null) {
                $suite = TestSuite::empty('CLI Arguments');
            }

            foreach ($files as $file) {
                if ($this->skipper->canSkipLoading($file, [])) {
                    continue;
                }

                $this->skipper->startRecording($file);

                try {
                    $suite->addTestFile($file, [], $numberOfRuns, $maxAttempts);
                } catch (Throwable $t) {
                    $this->skipper->abortRecording();

                    throw $t;
                }

                $this->skipper->stopRecording();
            }

            return $suite;
        }

        /*
         * A file that was named on its own is loaded even when it cannot
         * contribute a test to the run: not loading it would save nothing, and
         * the test suite that is built for it is named after the test class in
         * it, which is not known while the file is not loaded.
         */
        if ($suite !== null && $this->skipper->canSkipLoading($path, [])) {
            return $suite;
        }

        try {
            $testClass = (new TestSuiteLoader)->load($path);
        } catch (Exception $e) {
            print $e->getMessage() . PHP_EOL;

            exit(1);
        }

        $this->skipper->startRecording($path);

        try {
            if ($suite === null) {
                $result = TestSuite::fromClassReflector($testClass, [], $numberOfRuns, $maxAttempts);
            } else {
                $suite->addTestSuite($testClass, [], $numberOfRuns, $maxAttempts);

                $result = $suite;
            }
        } catch (Throwable $t) {
            $this->skipper->abortRecording();

            throw $t;
        }

        $this->skipper->stopRecording();

        return $result;
    }

    /**
     * @param list<non-empty-string> $paths
     * @param list<non-empty-string> $suffixes
     * @param positive-int           $numberOfRuns
     * @param positive-int           $maxAttempts
     *
     * @throws \PHPUnit\Framework\Exception
     */
    private function testSuiteFromPathList(array $paths, array $suffixes, int $numberOfRuns, int $maxAttempts): TestSuite
    {
        $suite = TestSuite::empty('CLI Arguments');

        foreach ($paths as $path) {
            $this->testSuiteFromPath($path, $suffixes, $numberOfRuns, $maxAttempts, $suite);
        }

        return $suite;
    }
}
