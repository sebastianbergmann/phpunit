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
use function assert;
use function ini_get_all;
use function is_array;
use function is_string;
use function proc_close;
use function proc_open;
use function str_contains;
use function stream_get_contents;
use function unserialize;

/**
 * The INI settings that this process was started with and that a PHP process
 * started with the same binary, but without command-line options, does not
 * have: the settings given to the PHP binary with -d, for instance.
 *
 * A setting is compared by its global value, the value it had when the process
 * started, so that a setting changed at runtime with ini_set() is not among
 * them.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CommandLineIniSettings
{
    /**
     * @var ?list<non-empty-string>
     */
    private static ?array $settings = null;

    /**
     * @return list<non-empty-string>
     */
    public static function settings(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        self::$settings = [];

        $settingsWithoutOptions = self::globalValuesOfAProcessStartedWithoutOptions();

        if ($settingsWithoutOptions === null) {
            // @codeCoverageIgnoreStart
            return self::$settings;
            // @codeCoverageIgnoreEnd
        }

        $settings = ini_get_all(null, true);

        assert($settings !== false);

        foreach ($settings as $name => $setting) {
            assert(is_array($setting));

            // A setting of an extension that a process started without
            // options does not load cannot be given to such a process either.
            if (!isset($settingsWithoutOptions[$name])) {
                continue;
            }

            if (!isset($setting['global_value']) ||
                !is_string($setting['global_value']) ||
                $setting['global_value'] === $settingsWithoutOptions[$name]) {
                continue;
            }

            $value = $setting['global_value'];

            // A value that contains a line break cannot be given with -d.
            if (str_contains($value, "\n") || str_contains($value, "\r")) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            self::$settings[] = $name . '=' . $value;
        }

        return self::$settings;
    }

    /**
     * @return ?array<string, string>
     */
    private static function globalValuesOfAProcessStartedWithoutOptions(): ?array
    {
        $process = proc_open(
            [PHP_BINARY, '-r', 'echo serialize(ini_get_all(null, true));'],
            [1 => ['pipe', 'w']],
            $pipes,
        );

        if ($process === false) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        assert(isset($pipes[1]));

        $output = stream_get_contents($pipes[1]);

        proc_close($process);

        if (!is_string($output)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $settings = @unserialize($output, ['allowed_classes' => false]);

        if (!is_array($settings)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $values = [];

        foreach ($settings as $name => $setting) {
            if (!is_string($name) || !is_array($setting) || !isset($setting['global_value']) || !is_string($setting['global_value'])) {
                continue;
            }

            $values[$name] = $setting['global_value'];
        }

        return $values;
    }
}
