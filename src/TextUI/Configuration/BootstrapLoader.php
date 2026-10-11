<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function in_array;
use function is_readable;
use function sprintf;
use PHPUnit\Event\Emitter;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class BootstrapLoader
{
    private readonly Emitter $emitter;

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * @throws BootstrapScriptDoesNotExistException
     * @throws BootstrapScriptException
     */
    public function handle(Configuration $configuration): void
    {
        if (!$configuration->hasBootstrap()) {
            return;
        }

        $this->load($configuration->bootstrap());

        foreach ($configuration->bootstrapForTestSuite() as $testSuiteName => $bootstrapForTestSuite) {
            if ($configuration->includeTestSuites() !== [] && !in_array($testSuiteName, $configuration->includeTestSuites(), true)) {
                continue;
            }

            if ($configuration->excludeTestSuites() !== [] && in_array($testSuiteName, $configuration->excludeTestSuites(), true)) {
                continue;
            }

            $this->load($bootstrapForTestSuite);
        }
    }

    /**
     * @param non-empty-string $filename
     */
    private function load(string $filename): void
    {
        if (!is_readable($filename)) {
            throw new BootstrapScriptDoesNotExistException($filename);
        }

        try {
            include_once $filename;
        } catch (Throwable $t) {
            $message = sprintf(
                "Error in bootstrap script: %s:\n%s\n%s",
                $t::class,
                $t->getMessage(),
                $t->getTraceAsString(),
            );

            while (($t = $t->getPrevious()) !== null) {
                $message .= sprintf(
                    "\n\nPrevious error: %s:\n%s\n%s",
                    $t::class,
                    $t->getMessage(),
                    $t->getTraceAsString(),
                );
            }

            throw new BootstrapScriptException($message);
        }

        $this->emitter->testRunnerBootstrapFinished($filename);
    }
}
