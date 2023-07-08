<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use const PHP_VERSION;
use function explode;
use function in_array;
use function is_dir;
use function is_file;
use function str_contains;
use function version_compare;
use PHPUnit\Framework\Exception as FrameworkException;
use PHPUnit\Framework\TestSuite as TestSuiteObject;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\RuntimeException;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;
use SebastianBergmann\FileIterator\Facade;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteMapper
{
    /**
     * @psalm-param non-empty-string $xmlConfigurationFile,
     *
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function map(string $xmlConfigurationFile, TestSuiteCollection $configuration, string $filter, string $excludedTestSuites): TestSuiteObject
    {
        try {
            $filterAsArray         = $filter ? explode(',', $filter) : [];
            $excludedFilterAsArray = $excludedTestSuites ? explode(',', $excludedTestSuites) : [];
            $result                = TestSuiteObject::empty($xmlConfigurationFile);

            foreach ($configuration as $testSuiteConfiguration) {
                if (!empty($filterAsArray) && !in_array($testSuiteConfiguration->name(), $filterAsArray, true)) {
                    continue;
                }

                if (!empty($excludedFilterAsArray) && in_array($testSuiteConfiguration->name(), $excludedFilterAsArray, true)) {
                    continue;
                }

                $testSuite      = TestSuiteObject::empty($testSuiteConfiguration->name());
                $testSuiteEmpty = true;

                $exclude = [];

                foreach ($testSuiteConfiguration->exclude()->asArray() as $file) {
                    $exclude[] = $file->path();
                }

                foreach ($testSuiteConfiguration->directories() as $directory) {
                    if (!version_compare(PHP_VERSION, $directory->phpVersion(), $directory->phpVersionOperator()->asString())) {
                        continue;
                    }

                    $files = (new Facade)->getFilesAsArray(
                        $directory->path(),
                        $directory->suffix(),
                        $directory->prefix(),
                        $exclude,
                    );

                    if (!empty($files)) {
                        $testSuite->addTestFiles($files);

                        $testSuiteEmpty = false;
                    } elseif (!str_contains($directory->path(), '*') && !is_dir($directory->path())) {
                        throw new TestDirectoryNotFoundException($directory->path());
                    }
                }

                foreach ($testSuiteConfiguration->files() as $file) {
                    if (!is_file($file->path())) {
                        throw new TestFileNotFoundException($file->path());
                    }

                    if (!version_compare(PHP_VERSION, $file->phpVersion(), $file->phpVersionOperator()->asString())) {
                        continue;
                    }

                    $testSuite->addTestFile($file->path());

                    $testSuiteEmpty = false;
                }

                if (!$testSuiteEmpty) {
                    $result->addTest($testSuite);
                }
            }

            return $result;
        } catch (FrameworkException $e) {
            throw new RuntimeException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }
}
