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

use function is_dir;
use function is_file;
use function realpath;
use function substr;
use PHPUnit\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\TestFileNotFoundException;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteBuilder
{
    public function build(CliConfiguration $cliConfiguration, XmlConfiguration $xmlConfiguration): TestSuite
    {
        if ($cliConfiguration->hasArgument()) {
            $argument = realpath($cliConfiguration->argument());

            if (!$argument) {
                throw new TestFileNotFoundException($cliConfiguration->argument());
            }

            return $this->testSuiteFromPath(
                $argument,
                $this->testSuffixes($cliConfiguration)
            );
        }

        $includeTestSuite = '';

        if ($cliConfiguration->hasTestSuite()) {
            $includeTestSuite = $cliConfiguration->testSuite();
        } elseif ($xmlConfiguration->phpunit()->hasDefaultTestSuite()) {
            $includeTestSuite = $xmlConfiguration->phpunit()->defaultTestSuite();
        }

        return (new TestSuiteMapper)->map(
            $xmlConfiguration->testSuite(),
            $includeTestSuite,
            $cliConfiguration->hasExcludedTestSuite() ? $cliConfiguration->excludedTestSuite() : ''
        );
    }

    private function testSuffixes(CliConfiguration $cliConfiguration): array
    {
        $testSuffixes = ['Test.php', '.phpt'];

        if ($cliConfiguration->hasTestSuffixes()) {
            $testSuffixes = $cliConfiguration->testSuffixes();
        }

        return $testSuffixes;
    }

    /**
     * @psalm-param list<string> $suffixes
     */
    private function testSuiteFromPath(string $path, array $suffixes): TestSuite
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
        } catch (Exception $e) {
            print $e->getMessage() . PHP_EOL;

            exit(1);
        }

        return new TestSuite($testClass);
    }
}
