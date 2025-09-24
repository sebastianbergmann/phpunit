<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ExecutionOrder\Duration;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class FooTest extends TestCase
{
    public function testOne(): void
    {
        // sleep(3);

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        // sleep(4);

        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        // sleep(2);

        $this->assertTrue(true);
    }
}
