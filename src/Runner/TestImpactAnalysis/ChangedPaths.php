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

use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use function file;
use function trim;

/**
 * The files and directories that changed, read from where something else put
 * them.
 *
 * Naming a change set one path at a time does not compose with the tools that
 * know what changed: what "git diff --name-only" has to say is a list, one
 * path per line, and a list is what this reads. Such a list is usually piped
 * rather than written to a file, which is what "-" is for.
 *
 * An empty list is not the same as no list: it says that nothing changed, and
 * that is an answer rather than a failure to give one.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChangedPaths
{
    /**
     * Returns null when the list cannot be read: a list that is not there must
     * not be mistaken for a list that says that nothing changed.
     *
     * @param non-empty-string $file          the file to read the list from, or "-" for standard input
     * @param non-empty-string $standardInput the stream that "-" stands for
     *
     * @return ?list<non-empty-string>
     */
    public static function readFrom(string $file, string $standardInput = 'php://stdin'): ?array
    {
        if ($file === '-') {
            $file = $standardInput;
        }

        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return null;
        }

        $paths = [];

        foreach ($lines as $line) {
            $path = trim($line);

            /*
             * A line that has nothing on it names nothing. Version control
             * tools end their output with a newline, and a file that was
             * edited by hand tends to have a line or two like that as well.
             */
            if ($path === '') {
                continue;
            }

            $paths[] = $path;
        }

        return $paths;
    }
}
