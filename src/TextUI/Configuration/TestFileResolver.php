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

use const PHP_VERSION;
use function in_array;
use function is_dir;
use function is_file;
use function sprintf;
use function str_contains;
use function version_compare;
use PHPUnit\Event\Emitter;
use PHPUnit\Runner\Filter\CompiledGroupFilter;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
 * Resolves which test files the test suite for a configuration is built from,
 * without loading any of them.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestFileResolver
{
    private Emitter $emitter;

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * A test suite that has no test files is part of the result, a test file
     * is part of at most one test suite.
     *
     * @param list<non-empty-string> $includeTestSuites
     * @param list<non-empty-string> $excludeTestSuites
     *
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     *
     * @return list<array{name: non-empty-string, files: list<array{path: non-empty-string, groups: list<non-empty-string>}>}>
     */
    public function filesInTestSuites(TestSuiteCollection $configuredTestSuites, array $includeTestSuites, array $excludeTestSuites): array
    {
        $result    = [];
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
            $files         = [];

            foreach ($configuredTestSuite->exclude()->asArray() as $file) {
                $exclude[] = $file->path();
            }

            foreach ($configuredTestSuite->directories() as $directory) {
                if (!str_contains($directory->path(), '*') && !is_dir($directory->path())) {
                    throw new TestDirectoryNotFoundException($directory->path());
                }

                if (!version_compare(PHP_VERSION, $directory->phpVersion(), $directory->phpVersionOperator()->asString())) {
                    continue;
                }

                $filesInDirectory = (new FileIteratorFacade)->getFilesAsArray(
                    $directory->path(),
                    $directory->suffix(),
                    $directory->prefix(),
                    $exclude,
                );

                $groups = $directory->groups();

                $this->warnAboutGroupNamesThatCannotBeSelected($groups, $directory->path());

                foreach ($filesInDirectory as $file) {
                    if ($this->wasAlreadyAddedToAnotherTestSuite($processed, $file, $testSuiteName)) {
                        continue;
                    }

                    $processed[$file] = $testSuiteName;
                    $files[]          = ['path' => $file, 'groups' => $groups];
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

                $this->warnAboutGroupNamesThatCannotBeSelected($file->groups(), $file->path());

                $files[] = ['path' => $file->path(), 'groups' => $file->groups()];
            }

            $result[] = ['name' => $testSuiteName, 'files' => $files];
        }

        return $result;
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

            $this->emitter->testRunnerTriggeredPhpunitWarning(
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

        $this->emitter->testRunnerTriggeredPhpunitWarning(
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
