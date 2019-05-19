<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
trait ConfigurableMethods
{
    /**
     * @var ConfigurableMethods[]
     */
    private static $__phpunit_configurableMethods;

    public static function __phpunit_initConfigurableMethods(ConfigurableMethod ...$configurable): void
    {
        if (isset(static::$__phpunit_configurableMethods)) {
            throw new ConfigurableMethodsAlreadyInitializedException('Configurable methods is already initialized and can not be reinitialized.');
        }

        static::$__phpunit_configurableMethods = $configurable;
    }
}
