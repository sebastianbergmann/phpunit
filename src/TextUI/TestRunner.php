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

use PHPUnit\Event;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    private readonly Event\Emitter $emitter;

    public function __construct(Event\Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * @param ?list<non-empty-string> $selectedTests
     *
     * @throws RuntimeException
     */
    public function run(Configuration $configuration, TestRunHistory $testRunHistory, TestSuite $suite, ?array $selectedTests = null): void
    {
        new TestRunnerLifecycle($this->emitter)->run(
            $configuration,
            $testRunHistory,
            $suite,
            $selectedTests,
            static function () use ($suite): void
            {
                $suite->run();
            },
        );
    }
}
