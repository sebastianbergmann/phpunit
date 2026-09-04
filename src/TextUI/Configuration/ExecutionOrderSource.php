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

/**
 * The configuration surface a test execution order was configured through.
 *
 * Diagnostics have to name the surface they are about, and have to suggest a
 * replacement that exists on that surface.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This enumeration is not covered by the backward compatibility promise for PHPUnit
 */
enum ExecutionOrderSource
{
    /**
     * @return non-empty-string
     */
    public function subject(): string
    {
        if ($this === self::CommandLineOption) {
            return '--order-by';
        }

        return 'the executionOrder attribute';
    }

    /**
     * How to ask for dependency resolution on this configuration surface.
     *
     * @return non-empty-string
     */
    public function resolveDependencies(): string
    {
        if ($this === self::CommandLineOption) {
            return 'the --resolve-dependencies CLI option';
        }

        return 'the resolveDependencies="true" XML configuration attribute';
    }

    /**
     * How to ask for dependency resolution to be skipped on this configuration
     * surface.
     *
     * @return non-empty-string
     */
    public function ignoreDependencies(): string
    {
        if ($this === self::CommandLineOption) {
            return 'the --ignore-dependencies CLI option';
        }

        return 'the resolveDependencies="false" XML configuration attribute';
    }
    case CommandLineOption;
    case XmlAttribute;
}
