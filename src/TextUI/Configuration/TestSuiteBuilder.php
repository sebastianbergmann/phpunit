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

use const PHP_EOL;
use function assert;
use function count;
use function is_dir;
use function is_file;
use function realpath;
use function str_ends_with;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TextUI\RuntimeException;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;
use PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteBuilder
{
    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function build(Configuration $configuration): TestSuite
    {
        if ($configuration->hasCliArguments()) {
            $arguments = [];

            foreach ($configuration->cliArguments() as $cliArgument) {
                $argument = realpath($cliArgument);

                if (!$argument) {
                    throw new TestFileNotFoundException($cliArgument);
                }

                $arguments[] = $argument;
            }

            if (count($arguments) === 1) {
                $testSuite = $this->testSuiteFromPath(
                    $arguments[0],
                    $configuration->testSuffixes(),
                    $configuration->repeat(),
                );
            } else {
                $testSuite = $this->testSuiteFromPathList(
                    $arguments,
                    $configuration->testSuffixes(),
                    $configuration->repeat(),
                );
            }
        }

        if (!isset($testSuite)) {
            $xmlConfigurationFile = $configuration->hasConfigurationFile() ? $configuration->configurationFile() : 'Root Test Suite';

            assert($xmlConfigurationFile !== '');

            $testSuite = (new TestSuiteMapper)->map(
                $xmlConfigurationFile,
                $configuration->testSuite(),
                $configuration->ignoreTestSelectionInXmlConfiguration() ? [] : $configuration->includeTestSuites(),
                $configuration->ignoreTestSelectionInXmlConfiguration() ? [] : $configuration->excludeTestSuites(),
            );
        }

        EventFacade::emitter()->testSuiteLoaded(\PHPUnit\Event\TestSuite\TestSuiteBuilder::from($testSuite));

        return $testSuite;
    }

    /**
     * @param non-empty-string       $path
     * @param list<non-empty-string> $suffixes
     *
     * @throws \PHPUnit\Framework\Exception
     */
    private function testSuiteFromPath(string $path, array $suffixes, int $repeat, ?TestSuite $suite = null): TestSuite
    {
        if (str_ends_with($path, '.phpt') && is_file($path)) {
            if ($suite === null) {
                $suite = TestSuite::empty($path);
            }

            $suite->addTestFile($path, [], $repeat);

            return $suite;
        }

        if (is_dir($path)) {
            $files = (new FileIteratorFacade)->getFilesAsArray($path, $suffixes);

            if ($suite === null) {
                $suite = TestSuite::empty('CLI Arguments');
            }

            $suite->addTestFiles($files, $repeat);

            return $suite;
        }

        try {
            $testClass = (new TestSuiteLoader)->load($path);
        } catch (Exception $e) {
            print $e->getMessage() . PHP_EOL;

            exit(1);
        }

        if ($suite === null) {
            return TestSuite::fromClassReflector($testClass, repeat: $repeat);
        }

        $suite->addTestSuite($testClass, repeat: $repeat);

        return $suite;
    }

    /**
     * @param list<non-empty-string> $paths
     * @param list<non-empty-string> $suffixes
     *
     * @throws \PHPUnit\Framework\Exception
     */
    private function testSuiteFromPathList(array $paths, array $suffixes, int $repeat): TestSuite
    {
        $suite = TestSuite::empty('CLI Arguments');

        foreach ($paths as $path) {
            $this->testSuiteFromPath($path, $suffixes, $repeat, $suite);
        }

        return $suite;
    }
}
