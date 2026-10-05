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
use PHPUnit\TextUI\Configuration\AddedFilesAreReachedThroughChanges;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
 * The files that, when they are added, can only affect a test through a file
 * that was changed to use them.
 *
 * A file that was added and that no test is recorded as depending on is a
 * change nothing is known about, and every test is run because of it: code
 * that finds classes by looking at the file system, or by guessing their names
 * from a convention, can use a file that was added without any other file
 * changing. Where that does not happen is something only the project knows,
 * and it says so in its configuration.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FileReachedThroughChangesFinder
{
    /**
     * @return list<non-empty-string>
     */
    public function find(AddedFilesAreReachedThroughChanges $configuration): array
    {
        $files = [];

        foreach ($configuration->includeDirectories() as $directory) {
            foreach ((new FileIteratorFacade)->getFilesAsArray($directory->path(), $directory->suffix(), $directory->prefix()) as $file) {
                $files[$file] = true;
            }
        }

        foreach ($configuration->includeFiles() as $file) {
            $path = realpath($file->path());

            if ($path !== false && $path !== '') {
                $files[$path] = true;
            }
        }

        foreach ($configuration->excludeDirectories() as $directory) {
            foreach ((new FileIteratorFacade)->getFilesAsArray($directory->path(), $directory->suffix(), $directory->prefix()) as $file) {
                unset($files[$file]);
            }
        }

        foreach ($configuration->excludeFiles() as $file) {
            $path = realpath($file->path());

            if ($path !== false) {
                unset($files[$path]);
            }
        }

        $found = array_keys($files);

        sort($found);

        return $found;
    }
}
