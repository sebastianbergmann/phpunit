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

use function assert;
use function class_exists;
use function class_implements;
use function in_array;
use PHPUnit\Event;
use PHPUnit\Runner\ClassCannotBeInstantiatedException;
use PHPUnit\Runner\ClassDoesNotExistException;
use PHPUnit\Runner\ClassDoesNotImplementExtensionInterfaceException;
use PHPUnit\Runner\Exception;
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionClass;
use ReflectionException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExtensionBootstrapper
{
    private readonly Configuration $configuration;
    private readonly Facade $facade;

    public function __construct(Configuration $configuration, Facade $facade)
    {
        $this->configuration = $configuration;
        $this->facade        = $facade;
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param array<string, string> $parameters
     *
     * @throws Exception
     */
    public function bootstrap(string $className, array $parameters): void
    {
        if (!class_exists($className)) {
            throw new ClassDoesNotExistException($className);
        }

        if (!in_array(Extension::class, class_implements($className), true)) {
            throw new ClassDoesNotImplementExtensionInterfaceException($className);
        }

        try {
            $instance = (new ReflectionClass($className))->newInstance();
        } catch (ReflectionException $e) {
            throw new ClassCannotBeInstantiatedException($className, $e);
        }

        assert($instance instanceof Extension);

        $instance->bootstrap(
            $this->configuration,
            $this->facade,
            ParameterCollection::fromArray($parameters),
        );

        Event\Facade::emitter()->testRunnerBootstrappedExtension(
            $className,
            $parameters,
        );
    }
}
