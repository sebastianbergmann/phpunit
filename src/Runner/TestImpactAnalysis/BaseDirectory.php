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

use const DIRECTORY_SEPARATOR;
use function array_pop;
use function assert;
use function count;
use function explode;
use function getcwd;
use function implode;
use function preg_match;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * The directory the paths that are recorded are relative to.
 *
 * A path that is recorded as an absolute path names a file where it was when
 * it was recorded, and what was recorded cannot be used in another checkout of
 * the same project: in the working directory of another CI runner, for
 * instance, or in a container that mounts the project somewhere else. A path
 * that is recorded relative to the directory of the configuration file names
 * the same file in every checkout, because the configuration file is part of
 * the project it configures the tests of. When there is no configuration file,
 * paths are relative to the working directory.
 *
 * A path that is outside of this directory is recorded relative to it all the
 * same, with as many '..' as it takes: a directory next to it, another package
 * of the same repository for instance, is in the same place relative to it in
 * every checkout. A path that has no path relative to it, because it is on
 * another drive, or because it is not a path on the file system, is recorded
 * as it is.
 *
 * A path is recorded with '/' as the directory separator, on every platform,
 * and is read with the directory separator of the platform it is read on.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BaseDirectory
{
    /**
     * @var non-empty-string
     */
    private string $path;

    /**
     * The path is read with the directory separator of the platform, just
     * like the paths this directory is asked about: a path on Windows that is
     * written with '/' has no root otherwise, and no path would be relative
     * to it.
     *
     * @param non-empty-string $path an absolute path that names the directory the way realpath() does
     */
    public static function from(string $path): self
    {
        return new self(self::withNativeDirectorySeparators($path));
    }

    public static function fromWorkingDirectory(): self
    {
        $path = getcwd();

        if ($path === false) {
            return new self(DIRECTORY_SEPARATOR); // @codeCoverageIgnore
        }

        return new self($path);
    }

    /**
     * @param non-empty-string $path
     */
    private function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return non-empty-string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * The '.' and '..' in the path are worked out first: a path that is
     * configured relative to the configuration file is made absolute by
     * putting the directory of the configuration file in front of it, and not
     * by resolving it.
     *
     * A path that does not name where it is from a root is taken to be
     * relative to this directory already, and is returned as it is.
     *
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    public function relativePathOf(string $path): string
    {
        if (str_contains($path, '://')) {
            return $path;
        }

        $path = self::withNativeDirectorySeparators($path);
        $root = self::rootOf($path);

        if ($root === '' || $root !== self::rootOf($this->path)) {
            return self::withForwardSlashes($path);
        }

        $segmentsOfPath      = self::segmentsOf(substr($path, strlen($root)));
        $segmentsOfDirectory = self::segmentsOf(substr($this->path, strlen($root)));
        $shared              = 0;

        while ($shared < count($segmentsOfPath) &&
               $shared < count($segmentsOfDirectory) &&
               $segmentsOfPath[$shared] === $segmentsOfDirectory[$shared]) {
            $shared++;
        }

        $segments = [];

        for ($i = $shared; $i < count($segmentsOfDirectory); $i++) {
            $segments[] = '..';
        }

        for ($i = $shared; $i < count($segmentsOfPath); $i++) {
            $segments[] = $segmentsOfPath[$i];
        }

        if ($segments === []) {
            return '.';
        }

        return implode('/', $segments);
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    public function absolutePathOf(string $path): string
    {
        if (str_contains($path, '://')) {
            return $path;
        }

        $path = self::withNativeDirectorySeparators($path);

        if (self::rootOf($path) !== '') {
            return $path;
        }

        $root = self::rootOf($this->path);

        $absolutePath = $root . implode(
            DIRECTORY_SEPARATOR,
            self::segmentsOf(substr($this->path, strlen($root)) . DIRECTORY_SEPARATOR . $path),
        );

        if ($absolutePath === '') {
            return '.'; // @codeCoverageIgnore
        }

        return $absolutePath;
    }

    /**
     * What a path names where it is from, when it names where it is from
     * anything: the root of a file system, the root of a drive, or the server
     * and share a UNC path is on.
     */
    private static function rootOf(string $path): string
    {
        // @codeCoverageIgnoreStart
        if (DIRECTORY_SEPARATOR !== '/') {
            if (str_starts_with($path, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR)) {
                return DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
            }

            if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
                return substr($path, 0, 3);
            }
        }
        // @codeCoverageIgnoreEnd

        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return DIRECTORY_SEPARATOR;
        }

        return '';
    }

    /**
     * A path cannot climb out of the root it is below: there is nothing above
     * it.
     *
     * @return list<non-empty-string>
     */
    private static function segmentsOf(string $path): array
    {
        $segments = [];

        foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return $segments;
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private static function withNativeDirectorySeparators(string $path): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return $path;
        }

        // @codeCoverageIgnoreStart
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        assert($path !== '');

        return $path;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private static function withForwardSlashes(string $path): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return $path;
        }

        // @codeCoverageIgnoreStart
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        assert($path !== '');

        return $path;
        // @codeCoverageIgnoreEnd
    }
}
