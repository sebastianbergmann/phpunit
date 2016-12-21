<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Miguel Florido <miguel5fv@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Helper to abstract the management of the environment variables.
 */
class PHPUnit_Util_GlobalVar_Environment implements PHPUnit_Util_GlobalVar_Handler
{
    /**
     * {@inheritdoc}
     */
    public function setValue($key, $value, $force = false)
    {
        if (false === getenv($key) || true === $force) {
            putenv("{$key}={$value}");
        }
        if (!isset($_ENV[$key]) || true === $force) {
            $_ENV[$key] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($key)
    {
        if (false !== getenv($key)) {
            return getenv($key);
        }
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        return false;
    }
}
