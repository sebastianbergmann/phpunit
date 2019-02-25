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

use PHPUnit\Framework\TestCase;

/**
 * Class TearDownAfterClassTest
 *
 * Behaviour to test:
 * - tearDownAfterClass() errors do reach the user
 * - tests are executed
 * - tearDownAfterClass() should be called and reported once
 */
class TearDownAfterClassTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        throw new \Exception('forcing an Exception in tearDownAfterClass()');
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
