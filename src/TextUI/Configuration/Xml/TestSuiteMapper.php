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
use PHPUnit\Runner\Filter\CompiledGroupFilter;
use PHPUnit\Runner\TestIndex\NullTestFileSkipper;
use PHPUnit\Runner\TestIndex\TestFileSkipper;
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
    private TestFileSkipper $skipper;

    public function __construct(?TestFileSkipper $skipper = null)
    {
        if ($skipper === null) {
            $skipper = new NullTestFileSkipper;
        }

        $this->skipper = $skipper;
    }

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

                    $this->warnAboutGroupNamesThatCannotBeSelected($groups, $directory->path());

                    foreach ($files as $file) {
                        if ($this->wasAlreadyAddedToAnotherTestSuite($processed, $file, $testSuiteName)) {
                            continue;
                        }

                        /*
                         * A file that is not loaded is bookkept as if it were:
                         * whether a file was already added to another test
                         * suite, and whether a test suite has files at all,
                         * must not depend on whether the file has to be loaded.
                         */
                        $processed[$file] = $testSuiteName;
                        $empty            = false;

                        if ($this->skipper->canSkipLoading($file, $groups)) {
                            continue;
                        }

                        $this->skipper->record(
                            $file,
                            static function () use ($testSuite, $file, $groups, $numberOfRuns, $maxAttempts): void
                            {
                                $testSuite->addTestFile($file, $groups, $numberOfRuns, $maxAttempts);
                            },
                        );
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

                    $this->warnAboutGroupNamesThatCannotBeSelected($file->groups(), $file->path());

                    if ($this->skipper->canSkipLoading($file->path(), $file->groups())) {
                        continue;
                    }

                    $this->skipper->record(
                        $file->path(),
                        static function () use ($testSuite, $file, $numberOfRuns, $maxAttempts): void
                        {
                            $testSuite->addTestFile($file->path(), $file->groups(), $numberOfRuns, $maxAttempts);
                        },
                    );
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
     * The name of a group that the --group and --exclude-group CLI options
     * parse as a conjunction of the names of other groups cannot be used to
     * select the tests in it, see CompiledGroupFilter. The group is still
     * assigned to the tests: dropping it would take them out of a group the
     * test suite is expected to have.
     *
     * @param list<non-empty-string> $groups
     * @param non-empty-string       $path
     */
    private function warnAboutGroupNamesThatCannotBeSelected(array $groups, string $path): void
    {
        foreach ($groups as $group) {
            if (!CompiledGroupFilter::isConjunction($group)) {
                continue;
            }

            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Group name "%s" configured for %s cannot be used to select tests: "+" combines several group names into a selection of the tests that are in all of them',
                    $group,
                    $path,
                ),
            );
        }
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
