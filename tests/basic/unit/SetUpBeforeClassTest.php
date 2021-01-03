<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\SelfTest\Basic;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class SetUpBeforeClassTest.
 *
 * Behaviour to test:
 * - setUpBeforeClass() errors do reach the user
 * - setUp() is not run
 * - how many times is setUpBeforeClass() called?
 * - tests are not executed
 *
 * @see https://github.com/sebastianbergmann/phpunit/issues/2145
 * @see https://github.com/sebastianbergmann/phpunit/issues/3107
 * @see https://github.com/sebastianbergmann/phpunit/issues/3364
 */
class SetUpBeforeClassTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        throw new Exception('forcing an Exception in setUpBeforeClass()');
    }

    protected function setUp(): void
    {
        throw new Exception('setUp() should never have been run');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
