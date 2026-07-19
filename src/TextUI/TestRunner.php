<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\ResultCache;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    /**
     * @throws RuntimeException
     */
    public function run(Configuration $configuration, ResultCache $resultCache, TestSuite $suite): void
    {
        new TestRunnerLifecycle()->run(
            $configuration,
            $resultCache,
            $suite,
            static function () use ($suite): void
            {
                $suite->run();
            },
        );
    }
}
