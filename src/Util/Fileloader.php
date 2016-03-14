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
 * Utility methods to load PHP sourcefiles.
 *
 * @since Class available since Release 2.3.0
 */
class PHPUnit_Util_Fileloader
{
    /** @var Callable */
    private static $filename_rewrite_callback;

    /** @var Callable */
    private static $filename_restore_callback;

    /**
     * Provide callback for rewriting test file names that is called when loading suite files
     * @param callable $callback (source_filename => rewritten_filename)
     */
    public static function setFilenameRewriteCallback(Callable $callback)
    {
        self::$filename_rewrite_callback = $callback;
    }

    /**
     * Provide callback for restoring rewritten test file names
     * @param callable $callback (rewritten_filename => source_filename)
     */
    public static function setFilenameRestoreCallback(Callable $callback)
    {
        self::$filename_restore_callback = $callback;
    }

    public static function getFilenameRestoreCallback()
    {
        return self::$filename_restore_callback;
    }

    /**
     * Checks if a PHP sourcefile is readable.
     * The sourcefile is loaded through the load() method.
     *
     * @param string $filename
     *
     * @return string
     *
     * @throws PHPUnit_Framework_Exception
     */
    public static function checkAndLoad($filename)
    {
        $includePathFilename = stream_resolve_include_path($filename);

        if (!$includePathFilename || !is_readable($includePathFilename)) {
            throw new PHPUnit_Framework_Exception(
                sprintf('Cannot open file "%s".' . "\n", $filename)
            );
        }

        self::load($includePathFilename);

        return $includePathFilename;
    }

    /**
     * Loads a PHP sourcefile.
     *
     * @param string $filename
     *
     * @return mixed
     *
     * @since  Method available since Release 3.0.0
     */
    public static function load($filename)
    {
        $oldVariableNames = array_keys(get_defined_vars());

        if ($cb = self::$filename_rewrite_callback) {
            $new_filename = $cb($filename);
            include_once $new_filename;
        } else {
            include_once $filename;
        }

        $newVariables     = get_defined_vars();
        $newVariableNames = array_diff(
            array_keys($newVariables),
            $oldVariableNames
        );

        foreach ($newVariableNames as $variableName) {
            if ($variableName != 'oldVariableNames') {
                $GLOBALS[$variableName] = $newVariables[$variableName];
            }
        }

        return $filename;
    }
}
