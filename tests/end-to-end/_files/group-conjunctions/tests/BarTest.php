<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\GroupConjunctions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class BarTest extends TestCase
{
    #[Group('one')]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[Group('one')]
    #[Group('two')]
    public function testOneAndTwo(): void
    {
        $this->assertTrue(true);
    }

    #[Group('three')]
    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
