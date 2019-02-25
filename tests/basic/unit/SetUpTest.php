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
 * Class SetUpBeforeClassTest
 *
 * Behaviour to test:
 * - setUp() errors reacht he the user
 * - how many times is setUp() called?
 * - tests are not executed
 *
 * @see https://github.com/sebastianbergmann/phpunit/issues/3107
 * @see https://github.com/sebastianbergmann/phpunit/issues/3364
 */
class SetUpTest extends TestCase
{
    public function setUp(): void
    {
        throw new \RuntimeException('throw exception in setUp');
    }

    public function testOneWithSetUpException(): void
    {
        $this->fail();
    }

    public function testTwoWithSetUpException(): void
    {
        $this->fail();
    }
}
