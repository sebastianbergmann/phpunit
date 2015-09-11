<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Filesystem helpers.
 *
 * @since Class available since Release 3.0.0
 */
class PHPUnit_Util_Filesystem
{
    /**
     * @var array
     */
    protected static $buffer = [];

    /**
     * Maps class names to source file names:
     *   - PEAR CS:   Foo_Bar_Baz -> Foo/Bar/Baz.php
     *   - Namespace: Foo\Bar\Baz -> Foo/Bar/Baz.php
     *
     * @param  string $className
     * @return string
     * @since  Method available since Release 3.4.0
     */
    public static function classNameToFilename($className)
    {
        return str_replace(
            ['_', '\\'],
            DIRECTORY_SEPARATOR,
            $className
        ) . '.php';
    }
}
