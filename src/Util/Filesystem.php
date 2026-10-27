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
use function basename;
use function dirname;
use function fclose;
use function fileowner;
use function fileperms;
use function fstat;
use function is_dir;
use function is_link;
use function mkdir;
use function realpath;
use function str_starts_with;
use function tmpfile;

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
     * Creates a directory that only the user with the given ID can access.
     *
     * A directory that already exists is only accepted when it is not a
     * symbolic link, is owned by the user with the given ID, and cannot be
     * written to by other users. Otherwise, another user could have created
     * it to control what is read from it.
     */
    public static function createPrivateDirectory(string $directory, int $userId): bool
    {
        @mkdir($directory, 0o700);

        if (is_link($directory) || !is_dir($directory)) {
            return false;
        }

        if (@fileowner($directory) !== $userId) {
            return false;
        }

        $permissions = @fileperms($directory);

        return $permissions !== false && ($permissions & 0o022) === 0;
    }

    /**
     * Returns the ID of the user that owns the files that the current process
     * creates in the operating system's path used for temporary files.
     *
     * This is determined by creating a temporary file instead of by calling
     * posix_geteuid() so that it does not require the POSIX extension.
     */
    public static function currentUserId(): false|int
    {
        $handle = @tmpfile();

        if ($handle === false) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $status = fstat($handle);

        fclose($handle);

        if ($status === false) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return $status['uid'];
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
}
