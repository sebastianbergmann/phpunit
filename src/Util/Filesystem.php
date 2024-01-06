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

use function is_dir;
use function is_string;
use function mkdir;
use function realpath;
use function str_starts_with;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Filesystem
{
    public static function createDirectory(string $directory): bool
    {
        return !(!is_dir($directory) && !@mkdir($directory, 0o777, true) && !is_dir($directory));
    }

    /**
     * @psalm-param non-empty-string $path
     *
     * @return false|non-empty-string
     */
    public static function resolvePathOrStream(string $path): false|string
    {
        if (str_starts_with($path, 'php://') || str_starts_with($path, 'socket://')) {
            return $path;
        }

        $path = realpath($path);

        if (is_string($path) && !empty($path)) {
            return $path;
        }

        return false;
    }
}
