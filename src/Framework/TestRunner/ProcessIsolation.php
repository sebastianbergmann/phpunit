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

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;

/**
 * Decides whether a test is run in a separate process.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ProcessIsolation
{
    private Configuration $configuration;
    private Emitter $emitter;

    public function __construct(Emitter $emitter)
    {
        $this->configuration = ConfigurationRegistry::get();
        $this->emitter       = $emitter;
    }

    /**
     * A test is run in a separate process when the test or the configuration
     * requests it, unless the test already runs in isolation (it then is the
     * test in the separate process) or its requirements are not satisfied (it
     * then is skipped, which does not warrant a separate process).
     */
    public function shouldBeUsedFor(TestCase $test): bool
    {
        if ($test->isInIsolation()) {
            return false;
        }

        if (!$test->runsTestInSeparateProcess() && !$this->configuration->processIsolation()) {
            return false;
        }

        return new Requirements($this->emitter)->requirementsNotSatisfiedFor($test::class, $test->name()) === [];
    }
}
