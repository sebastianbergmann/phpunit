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
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionClass;
use Throwable;

/**
 * Bootstraps, in a parallel worker process, the configured extensions that
 * implement ChildProcessExtension.
 *
 * This is the worker's counterpart of ExtensionBootstrapper, and what
 * ChildProcessExtensionBootstrapper is for a separate process. An extension
 * that does not implement ChildProcessExtension is skipped without comment,
 * as is a class that does not exist: the main process has bootstrapped, or
 * warned about, every configured extension already, and the worker only
 * bootstraps the ones that asked to be. A failure to bootstrap an extension
 * in the worker is a new fact, though, and is recorded as a warning; the
 * worker has no unit to emit it into at the time it bootstraps, so it emits
 * the recorded warnings with the first unit it runs.
 *
 * The extensions that were bootstrapped successfully are shut down when the
 * worker stops (see shutdown()).
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class WorkerExtensionBootstrapper
{
    private readonly Configuration $configuration;
    private readonly ChildProcessFacade $facade;

    /**
     * @var list<ChildProcessExtension>
     */
    private array $extensions = [];

    /**
     * @var list<non-empty-string>
     */
    private array $warnings = [];

    public function __construct(Configuration $configuration, ChildProcessFacade $facade)
    {
        $this->configuration = $configuration;
        $this->facade        = $facade;
    }

    /**
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

            $this->extensions[] = $instance;
        } catch (Throwable $t) {
            $this->warnings[] = sprintf(
                'Bootstrapping of extension %s in a parallel worker process failed: %s%s%s',
                $className,
                $t->getMessage(),
                PHP_EOL,
                $t->getTraceAsString(),
            );
        }
    }

    /**
     * @return list<non-empty-string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * Shut down the extensions that were bootstrapped successfully, in the
     * order in which they were bootstrapped. An extension whose shutdown
     * fails does not keep the others from being shut down; the failure is
     * returned as a warning, which the worker reports to the main process.
     *
     * @return list<non-empty-string>
     */
    public function shutdown(): array
    {
        $warnings = [];

        foreach ($this->extensions as $extension) {
            try {
                $extension->shutdownChildProcess();
            } catch (Throwable $t) {
                $warnings[] = sprintf(
                    'Shutdown of extension %s in a parallel worker process failed: %s%s%s',
                    $extension::class,
                    $t->getMessage(),
                    PHP_EOL,
                    $t->getTraceAsString(),
                );
            }
        }

        return $warnings;
    }
}
