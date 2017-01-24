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
 * Helper to abstract the management of the environment variables.
 */
class PHPUnit_Util_GlobalVar_Factory
{
    /**
     * @param string $name
     *
     * @return PHPUnit_Util_GlobalVar_Handler
     *
     * @throws \InvalidArgumentException
     */
    public static function getGlobalVarHandler($name)
    {
        $candidateClassName = self::getClassNameCandidate($name);
        return self::createHandler($candidateClassName);
    }

    /**
     * @param string $candidateClassName
     *
     * @return PHPUnit_Util_GlobalVar_Handler
     *
     * @throws \InvalidArgumentException
     */
    private static function createHandler($candidateClassName)
    {
        $candidateForHandler = new $candidateClassName();

        if ($candidateForHandler instanceof PHPUnit_Util_GlobalVar_Handler)
        {
            return $candidateForHandler;
        }

        throw new \InvalidArgumentException('The global var handler must implement PHPUnit_Util_GlobalVar_Handler');
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private static function getClassNameCandidate($name)
    {
        $candidateClassName = "PHPUnit_Util_GlobalVar_$name";

        if (class_exists($candidateClassName)) {
            return $candidateClassName;
        }

        throw new \InvalidArgumentException("Not exists handler for the global var $name");
    }
}
