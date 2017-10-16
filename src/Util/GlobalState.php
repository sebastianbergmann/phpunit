<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use Closure;

class GlobalState
{
    /**
     * @var string[]
     */
    protected static $superGlobalArrays = [
        '_ENV',
        '_POST',
        '_GET',
        '_COOKIE',
        '_SERVER',
        '_FILES',
        '_REQUEST'
    ];

    /**
     * @return string
     */
    public static function getIncludedFilesAsString()
    {
        return static::processIncludedFilesAsString(\get_included_files());
    }

    /**
     * @param array $files
     *
     * @return string
     */
    public static function processIncludedFilesAsString(array $files)
    {
        $blacklist = new Blacklist;
        $prefix    = false;
        $result    = '';

        if (\defined('__PHPUNIT_PHAR__')) {
            $prefix = 'phar://' . __PHPUNIT_PHAR__ . '/';
        }

        for ($i = \count($files) - 1; $i > 0; $i--) {
            $file = $files[$i];

            if (!empty($GLOBALS['__PHPUNIT_ISOLATION_BLACKLIST']) &&
                \in_array($file, $GLOBALS['__PHPUNIT_ISOLATION_BLACKLIST'])) {
                continue;
            }

            if ($prefix !== false && \strpos($file, $prefix) === 0) {
                continue;
            }

            // Skip virtual file system protocols
            if (\preg_match('/^(vfs|phpvfs[a-z0-9]+):/', $file)) {
                continue;
            }

            if (!$blacklist->isBlacklisted($file) && \is_file($file)) {
                $result = 'require_once \'' . $file . "';\n" . $result;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public static function getIniSettingsAsString()
    {
        $result      = '';
        $iniSettings = \ini_get_all(null, false);

        foreach ($iniSettings as $key => $value) {
            $result .= \sprintf(
                '@ini_set(%s, %s);' . "\n",
                self::exportVariable($key),
                self::exportVariable($value)
            );
        }

        return $result;
    }

    /**
     * @return string
     */
    public static function getConstantsAsString()
    {
        $constants = \get_defined_constants(true);
        $result    = '';

        if (isset($constants['user'])) {
            foreach ($constants['user'] as $name => $value) {
                $result .= \sprintf(
                    'if (!defined(\'%s\')) define(\'%s\', %s);' . "\n",
                    $name,
                    $name,
                    self::exportVariable($value)
                );
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public static function getGlobalsAsString()
    {
        $result            = '';
        $superGlobalArrays = self::getSuperGlobalArrays();

        foreach ($superGlobalArrays as $superGlobalArray) {
            if (isset($GLOBALS[$superGlobalArray]) && \is_array($GLOBALS[$superGlobalArray])) {
                foreach (\array_keys($GLOBALS[$superGlobalArray]) as $key) {
                    if ($GLOBALS[$superGlobalArray][$key] instanceof Closure) {
                        continue;
                    }

                    $result .= \sprintf(
                        '$GLOBALS[\'%s\'][\'%s\'] = %s;' . "\n",
                        $superGlobalArray,
                        $key,
                        self::exportVariable($GLOBALS[$superGlobalArray][$key])
                    );
                }
            }
        }

        $blacklist   = $superGlobalArrays;
        $blacklist[] = 'GLOBALS';

        foreach (\array_keys($GLOBALS) as $key) {
            if (!\in_array($key, $blacklist) && !$GLOBALS[$key] instanceof Closure) {
                $result .= \sprintf(
                    '$GLOBALS[\'%s\'] = %s;' . "\n",
                    $key,
                    self::exportVariable($GLOBALS[$key])
                );
            }
        }

        return $result;
    }

    /**
     * @return string[]
     */
    protected static function getSuperGlobalArrays()
    {
        return self::$superGlobalArrays;
    }

    protected static function exportVariable($variable)
    {
        if (\is_scalar($variable) || null === $variable ||
            (\is_array($variable) && self::arrayOnlyContainsScalars($variable))) {
            return \var_export($variable, true);
        }

        return 'unserialize(' .
            \var_export(\serialize($variable), true) .
            ')';
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    protected static function arrayOnlyContainsScalars(array $array)
    {
        $result = true;

        foreach ($array as $element) {
            if (\is_array($element)) {
                $result = self::arrayOnlyContainsScalars($element);
            } elseif (!\is_scalar($element) && null !== $element) {
                $result = false;
            }

            if ($result === false) {
                break;
            }
        }

        return $result;
    }
}
