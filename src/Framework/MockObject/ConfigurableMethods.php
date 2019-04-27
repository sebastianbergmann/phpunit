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

trait ConfigurableMethods
{

    /**
     * @var ConfigurableMethods[]
     */
    private static $__phpunit_configurableMethods;

    public static function __phpunit_initConfigurableMethods(\PHPUnit\Framework\MockObject\ConfigurableMethod... $configurable)
    {
        if (isset(static::$__phpunit_configurableMethods)) {
            // TODO: improve exception
            throw new \Exception('nogo!');
        }
        static::$__phpunit_configurableMethods = $configurable;
    }
}
