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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteBuilder
{
    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function build(Configuration $configuration): TestSuite
    {
        if ($configuration->hasCliArgument()) {
            $argument = realpath($configuration->cliArgument());

            if (!$argument) {
                throw new TestFileNotFoundException($configuration->cliArgument());
            }

            $testSuite = $this->testSuiteFromPath(
                $argument,
                $configuration->testSuffixes()
            );
        }

        if (!isset($testSuite)) {
            $testSuite = (new TestSuiteMapper)->map(
                $configuration->testSuite(),
                $configuration->includeTestSuite(),
                $configuration->excludeTestSuite()
            );
        }

        EventFacade::emitter()->testSuiteLoaded(\PHPUnit\Event\TestSuite\TestSuite::fromTestSuite($testSuite));

        return $testSuite;
    }

    /**
     * @psalm-param list<string> $suffixes
     *
     * @throws \PHPUnit\Framework\Exception
     */
    private function testSuiteFromPath(string $path, array $suffixes): TestSuite
    {
        if (is_dir($path)) {
            $files = (new FileIteratorFacade)->getFilesAsArray($path, $suffixes);

            $suite = TestSuite::empty($path);
            $suite->addTestFiles($files);

            return $suite;
        }

        if (is_file($path) && str_ends_with($path, '.phpt')) {
            $suite = TestSuite::empty();
            $suite->addTestFile($path);

            return $suite;
        }

        try {
            $testClass = (new TestSuiteLoader)->load($path);
        } catch (Exception $e) {
            print $e->getMessage() . PHP_EOL;

            exit(1);
        }

        return TestSuite::fromClassReflector($testClass);
    }
}
