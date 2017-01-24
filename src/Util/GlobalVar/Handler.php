<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Defines the contract of the global variables handler classes.
 */
interface PHPUnit_Util_GlobalVar_Handler
{
    /**
     * Sets a new value for a given key.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $force
     */
    public function setValue($key, $value, $force = false);

    /**
     * Retrieve the value of the global var key given.
     *
     * @param string $key
     *
     * @return mixed|false if not exists
     */
    public function getValue($key);
}
