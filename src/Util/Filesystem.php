<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use const DIRECTORY_SEPARATOR;
use function assert;
use function basename;
use function defined;
use function dirname;
use function getcwd;
use function is_dir;
use function mkdir;
use function preg_match;
use function preg_split;
use function realpath;
use function rtrim;
use function str_contains;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Filesystem
{
    public static function createDirectory(string $directory): bool
    {
        return !(!is_dir($directory) && !@mkdir($directory, 0o777, true) && !is_dir($directory));
    }

    /**
     * @param non-empty-string $path
     *
     * @return false|non-empty-string
     */
    public static function resolveStreamOrFile(string $path): false|string
    {
        if (str_starts_with($path, 'php://') || str_starts_with($path, 'socket://')) {
            return $path;
        }

        $directory = dirname($path);

        if (is_dir($directory)) {
            return realpath($directory) . DIRECTORY_SEPARATOR . basename($path);
        }

        return false;
    }

    /**
     * Whether the path names a stream (such as php://stdout) rather than a file.
     *
     * @phpstan-assert-if-true non-empty-string $path
     */
    public static function isStream(string $path): bool
    {
        return str_contains($path, '://');
    }

    /**
     * @phpstan-assert-if-true non-empty-string $path
     */
    public static function isAbsolutePath(string $path): bool
    {
        if (str_starts_with($path, '/')) {
            return true;
        }

        // Matches the following on Windows:
        //  - \\NetworkComputer\Path
        //  - \\.\D:
        //  - \\.\c:
        //  - C:\Windows
        //  - C:\windows
        //  - C:/windows
        //  - c:/windows
        if (defined('PHP_WINDOWS_VERSION_BUILD') &&
            $path !== '' &&
            ($path[0] === '\\' || (strlen($path) >= 3 && preg_match('#^[A-Z]:[/\\\\]#i', substr($path, 0, 3)) === 1))) {
            return true;
        }

        return false;
    }

    /**
     * Resolves a path that may not exist yet to the absolute path that
     * writing to it would actually write to.
     *
     * A relative path is resolved against the current working directory.
     * "." and ".." are resolved, and every component that exists is
     * resolved through realpath() so that a symbolic link cannot make the
     * path point somewhere else than it appears to.
     *
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    public static function resolvePath(string $path): string
    {
        if (!self::isAbsolutePath($path)) {
            $cwd = getcwd();

            assert($cwd !== false);

            $path = $cwd . DIRECTORY_SEPARATOR . $path;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            // @codeCoverageIgnoreStart
            $separators = '#[/\\\\]+#';
            // @codeCoverageIgnoreEnd
        } else {
            $separators = '#/+#';
        }

        [$root, $remainder] = self::splitRoot($path);

        $components = preg_split($separators, $remainder);

        assert($components !== false);

        $current = $root;

        foreach ($components as $component) {
            if ($component === '' || $component === '.') {
                continue;
            }

            if ($component === '..') {
                $current = dirname($current);

                continue;
            }

            $candidate = rtrim($current, '/\\') . DIRECTORY_SEPARATOR . $component;
            $real      = realpath($candidate);

            if ($real !== false) {
                $current = $real;

                continue;
            }

            $current = $candidate;
        }

        assert($current !== '');

        return $current;
    }

    /**
     * Whether $path is $directory itself or is located inside it.
     *
     * Both paths must have been resolved with resolvePath() or realpath().
     *
     * @param non-empty-string $path
     * @param non-empty-string $directory
     */
    public static function isInside(string $path, string $directory): bool
    {
        if ($path === $directory) {
            return true;
        }

        return str_starts_with($path, rtrim($directory, '/\\') . DIRECTORY_SEPARATOR);
    }

    /**
     * @param non-empty-string $path
     *
     * @return array{non-empty-string, string}
     */
    private static function splitRoot(string $path): array
    {
        // @codeCoverageIgnoreStart
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            if (preg_match('#^([A-Z]:)[/\\\\]#i', $path, $matches) === 1) {
                return [$matches[1] . DIRECTORY_SEPARATOR, substr($path, 3)];
            }

            if (preg_match('#^(\\\\\\\\[^/\\\\]+[/\\\\][^/\\\\]+)#', $path, $matches) === 1) {
                return [$matches[1], substr($path, strlen($matches[1]))];
            }
        }
        // @codeCoverageIgnoreEnd

        return [DIRECTORY_SEPARATOR, substr($path, 1)];
    }
}
