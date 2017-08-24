<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Runner;

use ReflectionClass;

/**
 * An interface to define how a test suite should be loaded.
 */
interface TestSuiteLoader
{
    /**
     * @param string $suiteClassName
     * @param string $suiteClassFile
     *
     * @return ReflectionClass
     */
    public function load($suiteClassName, $suiteClassFile = '');

    /**
     * @param ReflectionClass $aClass
     *
     * @return ReflectionClass
     */
    public function reload(ReflectionClass $aClass);
}
