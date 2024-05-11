<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class IsolatedTestRunnerRegistry
{
    private static ?IsolatedTestRunner $runner = null;

    public static function run(TestCase $test, bool $runEntireClass, bool $preserveGlobalState): void
    {
        if (self::$runner === null) {
            self::$runner = new SeparateProcessTestRunner;
        }

        self::$runner->run($test, $runEntireClass, $preserveGlobalState);
    }

    public static function set(IsolatedTestRunner $runner): void
    {
        self::$runner = $runner;
    }
}
