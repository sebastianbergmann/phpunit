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

use function array_keys;
use function realpath;
use function sort;
use PHPUnit\TextUI\Configuration\TestImpactAnalysis;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
 * The files outside of the code that is subject to code coverage analysis that
 * a change to is noticed even when no test is recorded as depending on it.
 *
 * What a test is recorded as depending on is what it executed of the code that
 * is subject to code coverage analysis. A test depends on more than that: on a
 * helper in the directory of its test suite, for instance, or on a
 * configuration file that is read rather than executed. When no test is
 * recorded as depending on such a file, a change to it would not be noticed at
 * all unless the file is watched, and not a single test would be run because
 * of it.
 *
 * The PHP files in the directories of the test suites are watched, together
 * with what the configuration says is watched. The test files are not: a test
 * file that changed changed the tests in it, which are recorded as depending
 * on it, and a test file that was added contains tests that nothing is known
 * about, which are run because of that.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class WatchedFileFinder
{
    /**
     * @return list<non-empty-string>
     */
    public function find(TestSuiteCollection $testSuites, TestImpactAnalysis $testImpactAnalysis): array
    {
        $files     = [];
        $testFiles = [];

        foreach ($testSuites as $testSuite) {
            $exclude = [];

            foreach ($testSuite->exclude() as $file) {
                $exclude[] = $file->path();
            }

            foreach ($testSuite->directories() as $directory) {
                foreach ((new FileIteratorFacade)->getFilesAsArray($directory->path(), '.php', '', $exclude) as $file) {
                    $files[$file] = true;
                }

                foreach ((new FileIteratorFacade)->getFilesAsArray($directory->path(), $directory->suffix(), $directory->prefix(), $exclude) as $file) {
                    $testFiles[$file] = true;
                }
            }

            foreach ($testSuite->files() as $file) {
                $path = realpath($file->path());

                if ($path !== false) {
                    $testFiles[$path] = true;
                }
            }
        }

        foreach ($testImpactAnalysis->watchedDirectories() as $directory) {
            foreach ((new FileIteratorFacade)->getFilesAsArray($directory->path(), $directory->suffix(), $directory->prefix()) as $file) {
                $files[$file] = true;
            }
        }

        foreach ($testImpactAnalysis->watchedFiles() as $file) {
            $path = realpath($file->path());

            if ($path !== false && $path !== '') {
                $files[$path] = true;
            }
        }

        $watched = [];

        foreach (array_keys($files) as $file) {
            if (isset($testFiles[$file])) {
                continue;
            }

            $watched[] = $file;
        }

        sort($watched);

        return $watched;
    }
}
