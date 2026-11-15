<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function defined;
use function dirname;
use function is_array;
use function is_file;
use function is_string;
use function realpath;
use function str_starts_with;
use PHPUnit\Util\ExcludeList;

/**
 * Loads the code of PHPUnit and of its dependencies when PHPUnit is used from
 * a Composer installation, so that no test triggers the loading of this code:
 * a test may have changed how, or whether, code can be loaded, by replacing a
 * stream wrapper or by registering an autoloader, for instance.
 *
 * The main process preloads this code before it runs tests, and so does every
 * worker process of a parallel test run, so that the tests a worker runs see
 * the same code loaded as the tests of a sequential run do.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Preloader
{
    public static function preload(): void
    {
        if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
            return;
        }

        $composerInstall = PHPUNIT_COMPOSER_INSTALL;

        // @codeCoverageIgnoreStart
        if (!is_string($composerInstall)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $classMapFile = dirname($composerInstall) . '/composer/autoload_classmap.php';

        // @codeCoverageIgnoreStart
        if (!is_file($classMapFile)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $classMap = require $classMapFile;

        // @codeCoverageIgnoreStart
        if (!is_array($classMap)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $excludeList = new ExcludeList(true);

        foreach ($classMap as $codeUnitName => $sourceCodeFile) {
            // @codeCoverageIgnoreStart
            if (!is_string($codeUnitName) || !is_string($sourceCodeFile)) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            if (!str_starts_with($codeUnitName, 'PHPUnit\\') &&
                !str_starts_with($codeUnitName, 'SebastianBergmann\\')) {
                continue;
            }

            $sourceCodeFile = realpath($sourceCodeFile);

            if ($sourceCodeFile === false) {
                continue;
            }

            // only preload code that belongs to PHPUnit or one of its dependencies
            if (!$excludeList->isExcluded($sourceCodeFile)) {
                continue;
            }

            require_once $sourceCodeFile;
        }
    }
}
