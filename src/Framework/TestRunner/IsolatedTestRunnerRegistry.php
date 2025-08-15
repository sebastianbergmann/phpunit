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

use PHPUnit\Event\Facade;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class IsolatedTestRunnerRegistry
{
    private static ?IsolatedTestRunner $runner             = null;
    private static ?PcntlForkJobRunner $pcntlForkJobRunner = null;

    public static function run(TestCase $test, bool $runEntireClass, bool $preserveGlobalState): void
    {
        if (self::$runner === null) {
            self::$runner = new SeparateProcessTestRunner;
        }

        $pcntlForkJobRunner = self::pcntlForkRunner();

        if ($pcntlForkJobRunner->canRun($test, $runEntireClass, $preserveGlobalState)) {
            $pcntlForkJobRunner->run($test, $runEntireClass, $preserveGlobalState);

            return;
        }

        self::$runner->run($test, $runEntireClass, $preserveGlobalState);
    }

    public static function set(IsolatedTestRunner $runner): void
    {
        self::$runner = $runner;
    }

    private static function pcntlForkRunner(): PcntlForkJobRunner
    {
        if (self::$pcntlForkJobRunner === null) {
            self::$pcntlForkJobRunner = new PcntlForkJobRunner(
                new ChildProcessResultProcessor(
                    Facade::instance(),
                    Facade::emitter(),
                    PassedTests::instance(),
                    CodeCoverage::instance(),
                ),
            );
        }

        return self::$pcntlForkJobRunner;
    }
}
