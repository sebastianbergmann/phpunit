<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use function assert;
use function defined;
use function get_include_path;
use function sys_get_temp_dir;
use function tempnam;
use function var_export;
use PHPUnit\Framework\ProcessIsolationException;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\SourceMapper;
use SebastianBergmann\Template\Template;

/**
 * Assembles the code that boots PHPUnit in a child process, shared by every
 * kind of child process the test runner spawns: the per-test process of a test
 * that requires process isolation, and the persistent worker process of a
 * parallel run.
 *
 * The boot code comes in two fragments that the child process templates embed:
 * the head, which captures output, adjusts the include path, and loads the
 * autoloader; and the configuration fragment, which loads the parent's
 * configuration and source map and runs the configured bootstrap scripts.
 *
 * The configuration and the source map are written to temporary files that the
 * child processes read; both are the same for every child of one test run, so
 * each is written once and shared by all of them.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChildProcessBootstrap
{
    private static ?string $configurationFile = null;
    private static ?string $sourceMapFile     = null;

    /**
     * The head of a child process template: rendered with the INI settings the
     * child is to apply — an empty string when there are none.
     */
    public static function headFragment(string $iniSettings): string
    {
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        } else {
            // @codeCoverageIgnoreStart
            $composerAutoload = '\'\'';
            // @codeCoverageIgnoreEnd
        }

        if (defined('__PHPUNIT_PHAR__')) {
            // @codeCoverageIgnoreStart
            $phar = var_export(__PHPUNIT_PHAR__, true);
            // @codeCoverageIgnoreEnd
        } else {
            $phar = '\'\'';
        }

        $includePath = var_export(get_include_path(), true);

        $template = new Template(__DIR__ . '/templates/child-process-head.tpl');

        $template->setVar(
            [
                'iniSettings'      => $iniSettings,
                'include_path'     => "'." . $includePath . ".'",
                'composerAutoload' => $composerAutoload,
                'phar'             => $phar,
            ],
        );

        return $template->render();
    }

    /**
     * @throws ProcessIsolationException
     */
    public static function configurationFragment(): string
    {
        $bootstrap = '';

        if (ConfigurationRegistry::get()->hasBootstrap()) {
            $bootstrap = ConfigurationRegistry::get()->bootstrap();
        }

        $template = new Template(__DIR__ . '/templates/child-process-configuration.tpl');

        $template->setVar(
            [
                'serializedConfiguration' => self::configurationFile(),
                'sourceMapFile'           => self::sourceMapFile(),
                'bootstrap'               => $bootstrap,
            ],
        );

        return $template->render();
    }

    /**
     * @throws ProcessIsolationException
     */
    private static function configurationFile(): string
    {
        if (self::$configurationFile !== null) {
            return self::$configurationFile;
        }

        $path = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($path === false) {
            // @codeCoverageIgnoreStart
            throw new ProcessIsolationException;
            // @codeCoverageIgnoreEnd
        }

        if (!ConfigurationRegistry::saveTo($path)) {
            // @codeCoverageIgnoreStart
            throw new ProcessIsolationException;
            // @codeCoverageIgnoreEnd
        }

        self::$configurationFile = $path;

        return self::$configurationFile;
    }

    private static function sourceMapFile(): string
    {
        if (self::$sourceMapFile !== null) {
            return self::$sourceMapFile;
        }

        if (!ConfigurationRegistry::get()->source()->notEmpty()) {
            // @codeCoverageIgnoreStart
            self::$sourceMapFile = '';

            return self::$sourceMapFile;
            // @codeCoverageIgnoreEnd
        }

        $path = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($path === false) {
            // @codeCoverageIgnoreStart
            self::$sourceMapFile = '';

            return self::$sourceMapFile;
            // @codeCoverageIgnoreEnd
        }

        if (!SourceMapper::saveTo($path, ConfigurationRegistry::get()->source())) {
            // @codeCoverageIgnoreStart
            self::$sourceMapFile = '';

            return self::$sourceMapFile;
            // @codeCoverageIgnoreEnd
        }

        assert($path !== '');

        self::$sourceMapFile = $path;

        return self::$sourceMapFile;
    }
}
