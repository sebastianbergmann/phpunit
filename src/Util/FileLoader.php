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

use PHPUnit\Framework\Exception;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class FileLoader
{
    /**
     * Checks if a PHP sourcecode file is readable. The sourcecode file is loaded through the load() method.
     *
     * As a fallback, PHP looks in the directory of the file executing the stream_resolve_include_path function.
     * We do not want to load the Test.php file here, so skip it if it found that.
     * PHP prioritizes the include_path setting, so if the current directory is in there, it will first look in the
     * current working directory.
     *
     * @throws Exception
     */
    public static function checkAndLoad(string $filename): string
    {
        $includePathFilename = \stream_resolve_include_path($filename);

        if (!$includePathFilename) {
            throw new Exception(
                \sprintf('Cannot open file "%s".' . "\n", $filename)
            );
        }

        $localFile = __DIR__ . \DIRECTORY_SEPARATOR . $filename;

        if ($includePathFilename === $localFile || !self::isReadable($includePathFilename)) {
            throw new Exception(
                \sprintf('Cannot open file "%s".' . "\n", $filename)
            );
        }

        self::load($includePathFilename);

        return $includePathFilename;
    }

    /**
     * Loads a PHP sourcefile.
     */
    public static function load(string $filename): void
    {
        $oldVariableNames = \array_keys(\get_defined_vars());

        include_once $filename;

        $newVariables     = \get_defined_vars();

        foreach (\array_diff(\array_keys($newVariables), $oldVariableNames) as $variableName) {
            if ($variableName !== 'oldVariableNames') {
                $GLOBALS[$variableName] = $newVariables[$variableName];
            }
        }
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/pull/2751
     */
    private static function isReadable(string $filename): bool
    {
        return @\fopen($filename, 'r') !== false;
    }
}
