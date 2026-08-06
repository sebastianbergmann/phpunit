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
use function in_array;
use function is_dir;
use function is_file;
use function sprintf;
use function str_contains;
use function version_compare;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Exception as FrameworkException;
use PHPUnit\Framework\TestSuite as TestSuiteObject;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\RuntimeException;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;
use SebastianBergmann\FileIterator\Facade;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteMapper
{
    /**
     * @param non-empty-string       $xmlConfigurationFile
     * @param list<non-empty-string> $includeTestSuites
     * @param list<non-empty-string> $excludeTestSuites
     * @param positive-int           $numberOfRuns
     * @param positive-int           $maxAttempts
     *
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function map(string $xmlConfigurationFile, TestSuiteCollection $configuredTestSuites, array $includeTestSuites, array $excludeTestSuites, int $numberOfRuns = 1, int $maxAttempts = 1): TestSuiteObject
    {
        try {
            $result    = TestSuiteObject::empty($xmlConfigurationFile);
            $processed = [];

            foreach ($configuredTestSuites as $configuredTestSuite) {
                if ($includeTestSuites !== [] && !in_array($configuredTestSuite->name(), $includeTestSuites, true)) {
                    continue;
                }

                if ($excludeTestSuites !== [] && in_array($configuredTestSuite->name(), $excludeTestSuites, true)) {
                    continue;
                }

                $testSuiteName = $configuredTestSuite->name();
                $exclude       = [];

                foreach ($configuredTestSuite->exclude()->asArray() as $file) {
                    $exclude[] = $file->path();
                }

                $testSuite = TestSuiteObject::empty($configuredTestSuite->name());
                $empty     = true;

                foreach ($configuredTestSuite->directories() as $directory) {
                    if (!str_contains($directory->path(), '*') && !is_dir($directory->path())) {
                        throw new TestDirectoryNotFoundException($directory->path());
                    }

                    if (!version_compare(PHP_VERSION, $directory->phpVersion(), $directory->phpVersionOperator()->asString())) {
                        continue;
                    }

                    $files = (new Facade)->getFilesAsArray(
                        $directory->path(),
                        $directory->suffix(),
                        $directory->prefix(),
                        $exclude,
                    );

                    $groups = $directory->groups();

                    foreach ($files as $file) {
                        if ($this->wasAlreadyAddedToAnotherTestSuite($processed, $file, $testSuiteName)) {
                            continue;
                        }

                        $processed[$file] = $testSuiteName;
                        $empty            = false;

                        $testSuite->addTestFile($file, $groups, $numberOfRuns, $maxAttempts);
                    }
                }

                foreach ($configuredTestSuite->files() as $file) {
                    if (!is_file($file->path())) {
                        throw new TestFileNotFoundException($file->path());
                    }

                    if (!version_compare(PHP_VERSION, $file->phpVersion(), $file->phpVersionOperator()->asString())) {
                        continue;
                    }

                    if ($this->wasAlreadyAddedToAnotherTestSuite($processed, $file->path(), $testSuiteName)) {
                        continue;
                    }

                    $processed[$file->path()] = $testSuiteName;
                    $empty                    = false;

                    $testSuite->addTestFile($file->path(), $file->groups(), $numberOfRuns, $maxAttempts);
                }

                if (!$empty) {
                    $result->addTest($testSuite);
                }
            }

            return $result;
            // @codeCoverageIgnoreStart
        } catch (FrameworkException $e) {
            throw new RuntimeException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param array<non-empty-string, non-empty-string> $processed
     * @param non-empty-string                          $file
     * @param non-empty-string                          $testSuiteName
     */
    private function wasAlreadyAddedToAnotherTestSuite(array $processed, string $file, string $testSuiteName): bool
    {
        if (!isset($processed[$file])) {
            return false;
        }

        EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
            sprintf(
                'Cannot add file %s to test suite "%s" as it was already added to test suite "%s"',
                $file,
                $testSuiteName,
                $processed[$file],
            ),
        );

        return true;
    }
}
