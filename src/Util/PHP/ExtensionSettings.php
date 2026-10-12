<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use const PHP_BINARY;
use const PHP_SAPI;
use function array_is_list;
use function array_keys;
use function array_merge;
use function fclose;
use function get_loaded_extensions;
use function in_array;
use function is_array;
use function is_resource;
use function is_string;
use function json_decode;
use function proc_close;
use function proc_open;
use function stream_get_contents;
use function strtolower;
use Closure;
use ReflectionExtension;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExtensionSettings
{
    /**
     * @var ?list<non-empty-string>
     */
    private static ?array $settings = null;

    /**
     * Returns the "extension" and "zend_extension" settings that a child
     * process needs to load the extensions that are loaded in the current
     * process but that it does not load from the INI files, for instance
     * because they were loaded using "php -d extension=...".
     *
     * PHP does not expose these settings, which is why they are inferred by
     * comparing the extensions loaded in the current process with those loaded
     * in a child process. A setting is only returned when a child process that
     * is started with it actually loads the extension: the warning that PHP
     * emits when an extension cannot be loaded would otherwise make every test
     * that is run in a child process error.
     *
     * Extensions loaded using dl() are not considered: the child process runs
     * the code that loads them, too.
     *
     * The settings are determined when this method is first called and then
     * reused.
     *
     * @param Closure(): list<string> $parameters returns the -d parameters (other than the ones for the settings returned by this method) that child processes are started with
     *
     * @return list<non-empty-string>
     */
    public static function forChildProcess(Closure $parameters): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        self::$settings = self::settings($parameters);

        return self::$settings;
    }

    /**
     * @param Closure(): list<string> $parameters
     *
     * @return list<non-empty-string>
     */
    private static function settings(Closure $parameters): array
    {
        if (PHP_SAPI !== 'cli') {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $parameters           = $parameters();
        $loadedInChildProcess = self::loadedExtensionsInChildProcess($parameters);

        if ($loadedInChildProcess === null) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $candidates     = [];
        $zendExtensions = [];

        foreach (get_loaded_extensions(true) as $name) {
            if (in_array($name, $loadedInChildProcess, true)) {
                continue;
            }

            $candidates['zend_extension=' . self::fileName($name)] = $name;
            $zendExtensions[]                                      = strtolower($name);
        }

        foreach (get_loaded_extensions() as $name) {
            if (in_array($name, $loadedInChildProcess, true)) {
                continue;
            }

            // An extension such as Xdebug registers both a Zend extension
            // ("Xdebug") and an extension ("xdebug"), but must only be loaded
            // once, using the "zend_extension" setting
            if (in_array(strtolower($name), $zendExtensions, true)) {
                continue;
            }

            if (new ReflectionExtension($name)->isTemporary()) {
                continue;
            }

            $candidates['extension=' . self::fileName($name)] = $name;
        }

        if ($candidates === []) {
            return [];
        }

        foreach (array_keys($candidates) as $setting) {
            $parameters[] = '-d';
            $parameters[] = $setting;
        }

        $loadedInChildProcess = self::loadedExtensionsInChildProcess($parameters);

        if ($loadedInChildProcess === null) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $settings = [];

        foreach ($candidates as $setting => $name) {
            if (in_array($name, $loadedInChildProcess, true)) {
                $settings[] = $setting;
            }
        }

        return $settings;
    }

    /**
     * @param list<string> $parameters
     *
     * @return ?list<string>
     */
    private static function loadedExtensionsInChildProcess(array $parameters): ?array
    {
        $process = proc_open(
            array_merge(
                [PHP_BINARY],
                $parameters,
                [
                    '-d',
                    'xdebug.mode=off',
                    '-r',
                    'print json_encode(array_merge(get_loaded_extensions(), get_loaded_extensions(true)));',
                ],
            ),
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (!is_resource($process) || !isset($pipes[1], $pipes[2])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        // The warnings that PHP emits for extensions it cannot load are discarded
        stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        if ($output === false) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $extensions = json_decode($output, true);

        if (!is_array($extensions) || !array_is_list($extensions)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        foreach ($extensions as $extension) {
            if (!is_string($extension)) {
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
            }
        }

        return $extensions;
    }

    /**
     * Maps the name of an extension to the name that the "extension" and
     * "zend_extension" settings resolve to a file in the extension directory.
     */
    private static function fileName(string $name): string
    {
        if ($name === 'Zend OPcache') {
            return 'opcache';
        }

        return strtolower($name);
    }
}
