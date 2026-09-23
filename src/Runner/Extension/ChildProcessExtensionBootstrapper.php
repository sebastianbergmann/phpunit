<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use const PHP_EOL;
use function assert;
use function class_exists;
use function class_implements;
use function in_array;
use function sprintf;
use PHPUnit\Event\Emitter;
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionClass;
use Throwable;

/**
 * Bootstraps, in the child process of a test that runs in a separate
 * process, the configured extensions that implement ChildProcessExtension.
 *
 * This is the child process' counterpart of ExtensionBootstrapper. An
 * extension that does not implement ChildProcessExtension is skipped without
 * comment, as is a class that does not exist: the main process has
 * bootstrapped, or warned about, every configured extension already. A
 * failure to bootstrap an extension in the child process is a new fact,
 * though, and is reported with a test runner warning that reaches the main
 * process with the other events of the child process.
 *
 * The extensions that were bootstrapped successfully are shut down after the
 * test has run (see shutdown()).
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ChildProcessExtensionBootstrapper
{
    private readonly Configuration $configuration;
    private readonly ChildProcessFacade $facade;
    private readonly Emitter $emitter;

    /**
     * @var list<ChildProcessExtension>
     */
    private array $extensions = [];

    public function __construct(Configuration $configuration, ChildProcessFacade $facade, Emitter $emitter)
    {
        $this->configuration = $configuration;
        $this->facade        = $facade;
        $this->emitter       = $emitter;
    }

    /**
     * @param non-empty-string      $className
     * @param array<string, string> $parameters
     */
    public function bootstrap(string $className, array $parameters): void
    {
        if (!class_exists($className)) {
            return;
        }

        if (!in_array(ChildProcessExtension::class, class_implements($className), true)) {
            return;
        }

        try {
            $instance = new ReflectionClass($className)->newInstance();

            assert($instance instanceof ChildProcessExtension);

            $instance->bootstrapChildProcess(
                $this->configuration,
                $this->facade,
                ParameterCollection::fromArray($parameters),
            );
        } catch (Throwable $t) {
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Bootstrapping of extension %s in a separate process failed: %s%s%s',
                    $className,
                    $t->getMessage(),
                    PHP_EOL,
                    $t->getTraceAsString(),
                ),
            );

            return;
        }

        $this->extensions[] = $instance;
    }

    /**
     * Shuts down the extensions that were bootstrapped successfully, in the
     * order in which they were bootstrapped. An extension whose shutdown
     * fails does not keep the others from being shut down.
     */
    public function shutdown(): void
    {
        foreach ($this->extensions as $extension) {
            try {
                $extension->shutdownChildProcess();
            } catch (Throwable $t) {
                $this->emitter->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Shutdown of extension %s in a separate process failed: %s%s%s',
                        $extension::class,
                        $t->getMessage(),
                        PHP_EOL,
                        $t->getTraceAsString(),
                    ),
                );
            }
        }

        $this->extensions = [];
    }
}
