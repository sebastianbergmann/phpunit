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
use function is_dir;
use function mkdir;
use function str_replace;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Filesystem
{
    /**
     * Maps class names to source file names.
     *
     *   - PEAR CS:   Foo_Bar_Baz -> Foo/Bar/Baz.php
     *   - Namespace: Foo\Bar\Baz -> Foo/Bar/Baz.php
     */
    public static function classNameToFilename(string $className): string
    {
        return str_replace(
            ['_', '\\'],
            DIRECTORY_SEPARATOR,
            $className
        ) . '.php';
    }

    public static function createDirectory(string $directory): bool
    {
        return !(!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory));
    }
}
