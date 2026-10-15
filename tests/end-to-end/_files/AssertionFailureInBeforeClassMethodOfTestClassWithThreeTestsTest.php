<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;

final class AssertionFailureInBeforeClassMethodOfTestClassWithThreeTestsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::fail('message');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
