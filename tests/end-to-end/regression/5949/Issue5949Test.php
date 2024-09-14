<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5949;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class Issue5949Test extends TestCase
{
    #[TestDox("Test 1. No dollar sign.\n")]
    public function test1(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox("Test 2. No dollar sign.\n")]
    public function test2(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox("Test 3. Dollar sign (\$).\n")]
    public function test3(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox("Test 4. No dollar sign.\n")]
    public function test4(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox("Test 5. Dollar \$ sign.\n           More text.\n")]
    public function test5(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox("Test 6. No dollar sign.\n")]
    public function test6(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox("Test 7. No dollar sign.\n")]
    public function test7(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox("Test 8. No dollar sign.\n")]
    public function test8(): void
    {
        $this->assertTrue(true);
    }
}
